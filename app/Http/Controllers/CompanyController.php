<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Orders;
use App\Models\Company;
use App\Models\Jobs;
use App\Models\CompaniesRating;
use App\Models\Employee;
use App\Models\EmployeesRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataProf = Company::where('user_id', Auth::user()->id)->first();

        if($dataProf == null)
            return response()->json([
            'error' => true, 
            'message' => 'Complete your company profile to continue!'
        ]);

        $dataProf1= User::where('id', Auth::user()->id)->first();

        $jobs = Jobs::where('company_id', $dataProf->id)->count();
        $applications = Jobs::where('company_id', $dataProf->id)
                            ->join('orders', 'orders.job_id', '=', 'jobs.id')
                            ->count();

        $stat = ['total_jobs' => $jobs, 'total_applications' => $applications];
        $combinedData = array_merge($dataProf->toArray(), $dataProf1->toArray());
        $combinedData = array_merge($combinedData, $stat);        
        return response()->json([
            'error' => false,
            'data' => $combinedData,
         ]);
    }

    public function getPosts(){
        $company=Company::where('user_id', Auth::user()->id)->first();

        if($company){
        
            $posts=Jobs::where('company_id',$company->id)->get();

            return response()->json([
                'error' => false,
                'message' => 'The Posts is gotten successfully',
                'data' => $posts
            ], 201);
        }
        
        return response()->json([
            'error' => true,
            'message' => "This user don't exist in company table.",
        ], 404);
    } 

     
    public function getAllEmployees()
    {
        $company = Company::where('user_id', Auth::user()->id)->first();
    
        if (!$company) {
            return response()->json([
                'error' => true,
                'message' => 'No associated company found for the current user.'
            ]);
        }
    
        $employees = Jobs::where('company_id', $company->id)
                        ->join('orders', 'orders.job_id', '=', 'jobs.id')
                        ->join('employee', 'orders.employee_id', '=', 'employee.id')
                        ->join('users', 'employee.user_id', '=', 'users.id')
                        ->where('orders.status', 1)
                        ->select('users.name', 'users.email')
                        ->get();
    
        return response()->json([
            'error' => false,
            'message' => 'The employees have been displayed successfully',
            'data' => $employees,
        ], 201);
    }
    

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'address' => ['required', 'string', 'max:255'],
            'profession' => ['required', 'string', 'max:255'],
            'services' => ['required', 'string'],
            'description' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = Company::updateOrCreate(
            ['user_id' => Auth::user()->id],  // Conditions to find the record
            [
                'profession'=> $request['profession'],  // Data to update or include in new record
                'services' => $request['services'],
                'description' => $request['description'],    
            ]
        );

        User::where('id',Auth::user()->id)->update([
            'name' =>$request['name'],
            'email' =>$request['email'],
            'image' =>$request['image'],
            'phone' =>$request['phone'],
            'address' =>$request['address'],
        ]);

        return response()->json([
            'error' => false,
            'message' => 'The company is added successfully',
            'data' => $company
        ], 201);

    }

    public function addPost(Request $request) 
    {

        $validator = Validator::make($request->all(), [
            'id' => ['integer','nullable'],
            'jobName' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'salary' => ['nullable', 'integer', 'min:0'], 
            'deadline' => ['nullable', 'date'], 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company=Company::where('user_id', Auth::user()->id)->first();

        if($company){

            $job=Jobs::updateOrCreate(
                ['id' => $request['id'], 'company_id'=>$company->id],  // Conditions to find the record
                [
                    'jobName'=>$request['jobName'],
                    'salary'=>$request['salary'],
                    'deadline'=>$request['deadline'],
                    'description'=>$request['description'],
                    'company_id'=>$company->id,
                ]
            );

            return response()->json([
                'error' => false,
                'message' => 'Job posted successfully',
                'data' => $job
            ], 201);
        }
        
        return response()->json([
            'error' => true,
            'message' => "This user don't exist in company table.",
        ], 404);

    }


    public function editStatus(Request $request) 
    {

        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer','exists:orders,id'],
            'status' => ['required', 'integer'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }


        $company=Company::where('user_id', Auth::user()->id)->first();
        $orders=Orders::where('id',$request['id'])->first();
        $jobs=Jobs::where('id',$orders->job_id)->first();

        if($company->id == $jobs->company_id){
            Orders::where('id',$request['id'])->update([
                'status' => $request['status'],
            ]);

            if($request['status']==0){
        
                return response()->json([
                    'error' => false,
                    'message' => 'Your order is under consideration',
                    'data' => $request['status']
                ], 201);
            }

            else if($request['status']==1){
        
                return response()->json([
                    'error' => false,
                    'message' => 'Fortunately, you have been accepted into our company',
                    'data' => $request['status']
                ], 201);
            }

            else if($request['status']==2){
                return response()->json([
                    'error' => false,
                    'message' => 'Unfortunately, you have been rejected',
                    'data' => $request['status']
                ], 201);
            }   

            else{
                return response()->json([
                    'error' => true,
                    'message' => "Only status is 0, 1 or 2",
                ], 404);
            }
        }

        return response()->json([
            'error' => true,
            'message' => "This post is not for this company",
        ], 404);

    }

    public function addRating(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => ['required', 'integer', 'exists:employee,id'],
            'rating' => ['required', 'integer', 'max:5', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = Company::where('user_id', Auth::user()->id)->first();

        if ($company) {
            $rating = EmployeesRating::updateOrCreate(
                [
                    'company_id' => $company->id,
                    'employee_id' => $request['id'],
                ],
                [
                    'rating' => $request['rating'],
                ]
            );

            return response()->json([
                'error' => false,
                'message' => $rating->wasRecentlyCreated ? "Your feedback is stored" : "Your feedback is updated",
                'data' => $rating,
            ], 201);

        } else {
            return response()->json([
                'error' => true,
                'message' => "This company doesn't have any content.",
            ], 404);
        }
    }
    public function getApplications($id){
        $applications = Orders::join('employee', 'orders.employee_id', '=', 'employee.id')
                            ->join('users', 'employee.user_id', '=', 'users.id')
                            ->where('job_id', $id)
                            ->select('users.name', 'users.email', 'users.image', 'users.phone', 'users.address', 'employee.skills', 'employee.university', 'orders.description', 'orders.status')
                            ->get();

        return response()->json(['error' => false, 'data' => $applications, 'message' => 'Your applications are there!']);
    }
}
