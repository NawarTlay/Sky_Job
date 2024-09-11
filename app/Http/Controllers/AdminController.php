<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\Orders;
use App\Models\Employee;
use App\Models\Company;
use App\Models\Jobs;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = Auth::user();
    
        // Check if the authenticated user is an admin (assuming role 2 is admin)
        if ($admin->role != 2) {
            return response()->json([
                'error' => true,
                'message' => "Only admins can suspend users!"
            ]);
        }
    
        // Fetch the user by id
        $user = User::find($id);
    
        // Check if the user exists
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => "User not found!"
            ]);
        }
    
        // Toggle the suspended status
        $user->suspended = $user->suspended == 1 ? 0 : 1;
    
        // Save the user
        $user->save();
    
        // Return the updated user status
        return response()->json([
            'error' => false,
            'message' => "User status updated successfully!",
            'data' => $user->suspended
        ]);
    }
    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function makeAdmin($id)
    {
        $admin = Auth::user();
    
        // Check if the authenticated user is an admin (assuming role 2 is admin)
        if ($admin->role != 2) {
            return response()->json([
                'error' => true,
                'message' => "Only admins can make accounts to them!"
            ]);
        }
    
        // Fetch the user by id
        $user = User::find($id);
    
        // Check if the user exists
        if (!$user) {
            return response()->json([
                'error' => true,
                'message' => "User not found!"
            ]);
        }

        $user->role = 2;
    
        // Save the user
        $user->save();
    
        return response()->json([
            'error' => false,
            'message' => "This User became Admin!",
            'data' => $user->role
        ]);
    }

    public function getStatistics(){

        $admin = Auth::user();

        // Check if the authenticated user is an admin (assuming role 2 is admin)
        if ($admin->role != 2) {
            return response()->json([
                'error' => true,
                'message' => "Only admins can access this data!"
            ]);
        }
        $allCompanies = User::where('role',0)->count();
        $allEmployees = User::where('role',1)->count();
        $allAdmins = User::where('role',2)->count();

        $completeCompanies = Company::count();
        $completeEmployees = Employee::count();
        $allApplications = Orders::count();
        $rejectedApplications = Orders::where('status',2)->count();
        $acceptedApplications = Orders::where('status',1)->count();
        $pendingApplications = Orders::where('status',0)->count();
        $jobs = Jobs::count();

        $ret = [
            'allCompanies' => $allCompanies,
            'allEmployees' => $allEmployees,
            'completeCompanies' => $completeCompanies,
            'InCompleteCompanies' => $allCompanies-$completeCompanies,
            'completeEmployees' => $completeEmployees,
            'InCompleteEmployees' => $allEmployees-$completeEmployees,
            'allAdmins' => $allAdmins,
            'allApplications' => $allApplications,
            'jobs' => $jobs,
            'rejectedApplications' => $rejectedApplications,
            'acceptedApplications' => $acceptedApplications,
            'pendingApplications' => $pendingApplications,
        ];

        return response()->json([
            'error' => false,
            'message' => "Statistics have been displayed successfully",
            'data' => $ret,
        ]);
    }
}
