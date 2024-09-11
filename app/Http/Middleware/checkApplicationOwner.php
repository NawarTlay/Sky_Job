<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;
use App\Models\Jobs;

class checkApplicationOwner
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $id = $request->route('id');
        
        $companyId = Jobs::where('jobs.id', $id)
                        ->join('company', 'jobs.company_id', '=', 'company.id')
                        ->select('company.user_id')
                        ->first();

        if($companyId == null || $companyId->user_id != Auth::user()->id)   
            return response()->json(['error' => true, 'message' => "UnAuthorized action!"], 400);

        return $next($request);
    }
}
