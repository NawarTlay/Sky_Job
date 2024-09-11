<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
class ChatController extends Controller
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
        $validator = Validator::make($request->all(), [
            'reciever_id' => ['required', 'integer'],
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => true,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $company = Company::where('id', $request->reciever_id)->first();

        if ($company==NULL) {
            $company = Employee::where('id', $request->reciever_id)->first();
        }

        $chats = Chat::create([
            'sender_id' => Auth::user()->id, 
            'reciever_id' => $company->user_id,
            'message' => $request['message'],
            'date' => now()
        ]);
    
        return response()->json([
            'error' => false,
            'message' => 'The message is added successfully',
            'data' => $chats
        ], 201);

    }

    public function deleteMessage($id)
    {
        // Find the message by ID
        $message = Chat::find($id);

        // Check if the message exists
        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }

        // Check if the authenticated user is the sender of the message
        if ($message->sender_id != auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Delete the message
        $message->delete();

        return response()->json(['success' => 'Message deleted successfully'], 200);
    }

    public function getMessages($id){
       
        $userId = Auth::user()->id;
        $usr = Company::where('id', $id)->select('user_id')->first();
        if($usr == NULL){
            $usr = Employee::where('id', $id)->select('user_id')->first();
        }

        $id = $usr->user_id;

        /*
        // التحقق مما إذا كان المستخدم الحالي موظفًا
        $employee = Employee::where('user_id', $userId)->first();
        
        if (!$employee) {
            return response()->json([
                'error' => true,
                'message' => "This employee doesn't have any content!",
            ], 404);
        }
        */
        $messages = Chat::where(function ($query) use ($id, $userId) {
            $query->where('sender_id', $id)
                  ->where('reciever_id', $userId);
        })->orWhere(function ($query) use ($id, $userId) {
            $query->where('sender_id', $userId)
                  ->where('reciever_id', $id);
        })->orderBy('date', 'asc')->get();
    
        return response()->json([
            'error' => false,
            'messages' => $messages,
        ], 200);
    }
    


    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function show(Chat $chat)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function edit(Chat $chat)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Chat $chat)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Chat  $chat
     * @return \Illuminate\Http\Response
     */
    public function destroy(Chat $chat)
    {
        //
    }
}
