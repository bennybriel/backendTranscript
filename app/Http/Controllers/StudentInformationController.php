<?php

namespace App\Http\Controllers;

use App\Models\StudentInformation;
use App\Http\Requests\StoreStudentInformationRequest;
use App\Http\Requests\UpdateStudentInformationRequest;
use App\Http\Resources\StudentInformationResource;
use App\Http\Requests\StudentInformationRequest;
use Illuminate\Support\Facades\DB;
class StudentInformationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login(Request $request)
    {
        // $user = User::where('email', '=', $request->email)->first();
        // if(!$user || !Hash::check($request->password, $user->password))
        // {
        //     return response()->json(
        //         [
        //            'message' => 'Invalid Credentials',
        //            'errors' =>
        //                     [
        //                         'password' => 'The password does not match to the user account.',
        //                     ]
        //         ]);
        // }
    }

    public function index()
    {
        //
        ///return  StudentInformationResource::collection(StudentInformation::all());
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
     * @param  \App\Http\Requests\StoreStudentInformationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StudentInformationRequest $request)
    {
        //

    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\StudentInformation  $studentInformation
     * @return \Illuminate\Http\Response
     */
    public function show($studentInformation)
    {

        if(!$studentInformation)
        {
            $message= $studentInformation. ' Please Supply Valid Student Matriculation Number';
            $response=[
                'status'=>400,
                'message'=>$message,
            ];
            return response()->json($response);
        }

        $data = StudentInformation::where('Matric', $studentInformation)->first();
        if($data)
        {
            return new StudentInformationResource($data);
        }
        else
        {
            $message= $studentInformation. ' Invalid Student Matriculation Number';
            $response=[
                'status'=>405,
                'message'=>$message
            ];
            return response()->json($response);
        }

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\StudentInformation  $studentInformation
     * @return \Illuminate\Http\Response
     */
    public function edit(StudentInformation $studentInformation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateStudentInformationRequest  $request
     * @param  \App\Models\StudentInformation  $studentInformation
     * @return \Illuminate\Http\Response
     */
    public function update(StudentInformationRequest $request, StudentInformation $studentInformation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\StudentInformation  $studentInformation
     * @return \Illuminate\Http\Response
     */
    public function destroy(StudentInformation $studentInformation)
    {
        //
    }
}
