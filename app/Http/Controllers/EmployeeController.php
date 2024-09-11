<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\EmployeesRating;
use App\Models\Company;
use App\Models\User;
use App\Models\Jobs;
use App\Models\Orders;
use App\Models\CompaniesRating;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dataProf = Employee::where('user_id', Auth::user()->id)->first();
        $dataProf1 = User::where('id', Auth::user()->id)->first();

        if($dataProf == NULL){
            $data = [
                'skills' => '',
                'university' => '',
                'user_id' => Auth::user()->id,
                "image"=> '',
                "phone"=> '',
                "address"=> '',
            ];
            $combinedData = array_merge($dataProf1->toArray(), $data);
        }
        else
            $combinedData = array_merge($dataProf->toArray(), $dataProf1->toArray());

        return response()->json([
            'error' => false,
            'data' => $combinedData,
        ]);
    }


    public function getPosts()
    {
        $user = User::where('id', Auth::user()->id)->first();
        $userRole = $user->role;

        if ($userRole) {
            // جلب مهارات الموظف
            $employee = Employee::where('user_id', Auth::user()->id)->first();

            if(!$employee){

                $posts = Jobs::join('company', 'jobs.company_id', '=', 'company.id')
                ->join('users', 'company.user_id', '=', 'users.id')
                ->select('jobs.company_id','jobs.id', 'jobs.jobName', 'jobs.salary', 'jobs.description', 'jobs.deadline', 'users.name', 'users.image', 'users.address')
                ->get();

               return response()->json([
                    'error' => false,
                    'message' => 'The Posts are displayed successfully',
                    'data' => $posts
                ], 201);

            }

            $employeeSkills = explode(',', $employee->skills); // نفترض أن المهارات مفصولة بفواصل

            // جلب فرص العمل
            $posts = Jobs::join('company', 'jobs.company_id', '=', 'company.id')
                            ->join('users', 'company.user_id', '=', 'users.id')
                            ->select('jobs.company_id','jobs.id', 'jobs.jobName', 'jobs.salary', 'jobs.description', 'jobs.deadline', 'users.name', 'users.image', 'users.address')
                            ->get();

            // حساب درجة التوافق
            foreach ($posts as $post) {
                $jobDescription = $post->description;
                $jobName = $post->jobName;
                $matchCount = 0;

                foreach ($employeeSkills as $skill) {
                    $skill = trim($skill);
                    if (stripos($jobDescription, $skill) !== false || stripos($jobName, $skill) !== false) {
                        $matchCount++;
                    }
                }

                $post->matchScore = $matchCount;
            }

            // ترتيب فرص العمل بناءً على درجة التوافق
            $sortedPosts = collect($posts)->sortByDesc('matchScore')->values()->all();

            return response()->json([
                'error' => false,
                'message' => 'The Posts are displayed successfully',
                'data' => $sortedPosts
            ], 201);
        }

        return response()->json([
            'error' => true,
            'message' => "This is a company not employee",
        ], 404);
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
            'skills' => ['required', 'string'],
            'university' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        
        $employee = Employee::updateOrCreate(
            ['user_id' => Auth::user()->id],  // Conditions to find the record
            [
                'skills' => $request['skills'],       // Data to update or include in new record
                'university' => $request['university'],
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
            'data' => $employee,
        ]);

    }

    public function addOrder(Request $request) 
    {

        $validator = Validator::make($request->all(), [
            'job_id' => ['required', 'integer','exists:jobs,id'],
            'description' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee=Employee::where('user_id', Auth::user()->id)->first();

        if($employee){
            $order=Orders::updateOrCreate(
                [
                    'employee_id'=> $employee->id,
                    'job_id'=> $request['job_id'],
                ], 

                ['description'=> $request['description'],]   
            );

            return response()->json([
                'error' => false,
                'message' => 'Job posted successfully',
                'data' => $order
            ], 201);
        }

        return response()->json([
            'error' => true,
            'message' => "This user doesn't exist in employee table.",
        ], 404);
    }

    public function addRating(Request $request,$id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => ['required', 'integer', 'max:5', 'min:0'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $employee = Employee::where('user_id', Auth::user()->id)->first();

        if ($employee) {
            $rating = CompaniesRating::updateOrCreate(
                [
                    'company_id' => $id,
                    'employee_id' => $employee->id,
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
        } 
        else {
            return response()->json([
                'error' => true,
                'message' => "This employee doesn't have any content.",
            ], 404);
        }
    }


    public function getMyApplications(){

        // Check if the authenticated user is associated with an employee
        $employeeExists = Employee::where('user_id', Auth::user()->id)->exists();

        if (!$employeeExists) {
            return response()->json([
                'error' => true,
                'message' => 'No associated employee found for the current user.'
            ]);
        }

        $applications = Orders::join('employee', 'orders.employee_id', '=', 'employee.id')
                                ->where('employee.user_id', Auth::user()->id)
                                ->select('orders.job_id','orders.description', 'orders.status')
                                ->get();

        
        return response()->json([
            'error' => false, 
            'data' => $applications, 
            'message' => 'Your applications are there!'
        ]);
    }

    public function getPostsOneCompany($id){
        
        // Check if the authenticated user is associated with a company
        $companyExists = Company::where('user_id', $id)->exists();

        if (!$companyExists) {
            return response()->json([
                'error' => true,
                'message' => 'No associated company found for the current user.'
            ]);
        }

        $postsOneCompany = Jobs::join('company', 'jobs.company_id', '=', 'company.id')
                                ->where('company.user_id', $id)
                                ->select('jobs.jobName','jobs.salary','jobs.description','jobs.deadline')
                                ->get();

        
        return response()->json([
            'error' => false, 
            'data' => $postsOneCompany, 
            'message' => 'The posts are there!'
        ]);
    }

    
    public function getAllCompanies(){
        $companies = Company::join('users', 'company.user_id', '=', 'users.id')
        ->select('company.id','company.profession','company.services','company.description','users.name','users.email','users.image','users.phone','users.address')
        ->get();

        return response()->json([
            'error' => false,
            'message' => 'companies are displayed successfully',
            'data' => $companies
        ], 201);
    }


    public function getProfileOneCompany($id){
        
        $profileCompany = Company::join('users', 'company.user_id', '=', 'users.id')
                            ->where('company.id',$id)
                            ->select('users.name','users.email','users.phone', 'users.image', 'users.address','company.profession','company.services','company.description')
                            ->first();
    
        return response()->json([
            'error' => false,
            'message' => 'The Profile of company is displayed successfully',
            'data' => $profileCompany
        ], 201);
        
    }


    public function getNotification(){

        $employeeId = Employee::where('user_id', Auth::user()->id)->first()->id;

        $notifications = Orders::join('jobs', 'orders.job_id', '=', 'jobs.id')
            ->where('orders.employee_id', $employeeId)
            ->join('company', 'jobs.company_id', '=', 'company.id')
            ->join('users', 'company.user_id', '=', 'users.id')
            ->select('users.name','jobs.jobName', 'company.id', 'orders.status')
            ->get();

         // Filter out notifications with status 0
        $filteredNotifications = $notifications->filter(function ($notification) {
            return $notification->status != 0;
        });

        // Add the message based on the order status
        $filteredNotifications->each(function ($notification) {
            if ($notification->status == 1) {
                $notification->message = "You have been accepted by the company " . $notification->name." on The job ". $notification->jobName;
            } elseif ($notification->status == 2) {
                $notification->message = "You have been rejected by the company " . $notification->name." on The job ". $notification->jobName;
            }
        });

        return response()->json([
            'error' => false,
            'message' => 'Notifications are displayed successfully',
            'data' => $filteredNotifications->values() // Reset the keys of the collection
        ], 201);

    }

    // public function getApplications($id){ needs some updates, to preview the posts I applyed
    //     $applications = Orders::join('employee', 'orders.employee_id', '=', 'employee.id')
    //                         ->join('users', 'employee.user_id', '=', 'users.id')
    //                         ->where('job_id', $id)
    //                         ->select('users.name', 'users.email', 'users.image', 'users.phone', 'users.address', 'employee.skills', 'employee.university', 'orders.description', 'orders.status')
    //                         ->get();

    //     return response()->json(['error' => false, 'data' => $applications, 'message' => 'Your applications are there!']);
    // }
}
