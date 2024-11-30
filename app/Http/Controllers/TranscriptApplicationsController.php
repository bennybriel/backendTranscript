<?php

namespace App\Http\Controllers;
use App\Models\TranscriptApplication;
use App\Models\User;
use App\Http\Requests\UpdateTranscriptApplicationRequest;
use App\Http\Requests\TranscriptApplicationRequest;
use App\Http\Resources\TranscriptApplicationResource;
use App\Models\Registration;
use Illuminate\Support\Facades\Validator;
use Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\SaveConfig\Support\SaveModel;
use Auth;
use App\Models\PaymentRecord;
use App\Models\RequestLogger;
use App\Models\CompleteApplication;
use Illuminate\Support\Facades\Session;

class TranscriptApplicationsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        Session::flush();
        ///Session::regenerateToken();
    }
    public function getApply(Request $request)
    {

        Auth::logout();

        $req = $request->all();
        $islogin =true;
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],
        ]);

         $m = DB::table('users')->where('email', $req["email"])->first();
         if($m)
         {
             return  response()->json([
                                'affectedRows' =>0,
                                'data'=>0,
                                'statuscode'=>1
                                ]);
         }

          if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
          }

             //$data  = DB::table('studentinformation')->where('Matric',$req["matricno"])->first();
             $check = DB::table('users')->where('matricno', $req["matricno"])->first();
             //return $data;

                if($check)
                {
                         if(Auth::attempt(['email' => $check->email, 'password' => $check->matricno]))
                         {

                             //$user = Auth::user();
                             $u = User::where('email',$check->email)->first();
                             //$token = $user->createToken('transcriptApp')->accessToken;

                             return response()->json(['affectedRows' =>1,
                                     'data'=>$check,
                                     //'token'=>$token,
                                     'matricno'=>$check->matricno,
                                     'statuscode'=>0
                             ]);
                         }
                         else
                         {
                            return  response()->json([
                                 'affectedRows' =>0,
                                 'message'=>'Authenticated Failed',
                                 'data'=>0,
                                 'statuscode'=>1
                                 ]);
                         }
                }
                else
                {
                    $ck = DB::table('users')->where('matricno', $req["matricno"])->first();
                    $datas["name"] =  $req["name"];
                    $datas["email"] = $req["email"];
                    $datas["usertype"] ="User";
                    $datas["guid"]  = Str::uuid();
                    $datas["phone"] =    $req["phone"];
                    $datas["matricno"] = $req["matricno"];
                    $datas["password"] = Hash::make($req["matricno"]);
                    if(!$ck)
                    {
                        //Create account in the user table
                        $res =  (new SaveModel(new Registration(), $datas))->execute();
                        if($res)
                        {
                            $cks = DB::table('users')->where('matricno', $req["matricno"])->first();
                           if(Auth::attempt(['Email' => $cks->email, 'password' => $cks->matricno]))
                           {

                               $u = User::where('email',$cks->email)->first();
                               return response()->json(['affectedRows' =>1,
                                        'data'=>$u,
                                        'matricno'=> $req["matricno"],
                                        'statuscode'=>0
                               ]);
                           }
                        }
                        else
                        {
                         return  response()->json([
                             'affectedRows' =>0,
                             'message'=>'Account Creation Failed',
                             'data'=>0,
                             'statuscode'=>1
                             ]);
                        }
                    }
                }





    }
    public function transcriptRecord(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required']]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }

        $data = DB::SELECT('CALL GetRegisteredTranscriptByMatricNo(?)',array($req["matricno"]));
        if(count($data))
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }

    }
    public function queryTransaction($tid)
    {
        if($tid)
        {
            $matricno = $tid;
            $client = new \GuzzleHttp\Client;
            $yr =date("Y");

            try
            {
                $client = new \GuzzleHttp\Client();
                $url = config('baseUrl.trans_status_url') . $tid;
                $this->SaveLogggers($url);

                $response = $client->request('GET', $url, ['verify' => false, 'headers' => ['token' => 'funda123']]);

                if ($response->getStatusCode() == 200)
                {

                    $res = json_decode($response->getBody());

                    $this->SaveLogggers(json_encode($res));
                    //perform your action with $response

                        if ($res->status == "Approved Successful")
                         {
                            $sav = DB::UPDATE('CALL UserIspaidStatus(?,?)', array($res->trans_ref, $res->status));
                            DB::table('u_g_student_accounts')->where('transactionid',$tid)->update(['ispaid'=>1]);
                            return  response(['message' => 'Recorded Updated Successfully','statuscode'=>0]);
                         }
                        else
                        {

                            DB::table('u_g_student_accounts')->where('transactionid',$tid)->update(['response'=>$res->status]);
                            return  response(['message' => 'Recorded Not Updated Successfully','statuscode'=>1,'response'=>$res->status]);
                        }


                }
            }
            catch (\GuzzleHttp\Exception\RequestException $e)
            {
                $error['error'] = $e->getMessage();
                $error['request'] = $e->getRequest();
                if ($e->hasResponse()) {
                    if ($e->getResponse()->getStatusCode() == '400') {
                        $error['response'] = $e->getResponse();
                    }
                }

            }
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }

    }
    public function completeApplications(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required'],  'names' => ['required'],
         'organization' => ['required', 'string'],
         'contactperson' => ['required', 'string'],  'email' => ['required', 'string'],  'phone' => ['required', 'string'],
         'address1' => ['required', 'string'],  'address2' => ['required', 'string'],  'paymentref' => ['required', 'string'],
        ]);
         $matricno = $req["matricno"];
        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }
            $tkid = substr(mt_rand(1111, 9999).mt_rand(1111, 9999).mt_rand(1111, 9999), 1,4);
            $u = DB::table('applications')->where('transactionID',$req["paymentref"])->first();
            $transcriptID = "TR".$matricno.$tkid;
            // Save to request logger for tracking purpose
            $transcriptID      = $transcriptID;
            $data["matricno"]  = $matricno;
            $data["paymentref"] = $req["paymentref"];
            $data["organization"] = $req["organization"];
            $data["contactperson"] = $req["contactperson"];
            $data["email"] = $req["email"];
            $data["phone"] = $req["phone"];
            $data["address1"] = $req["address1"];
            $data["address2"] = $req["address2"];
            $data["transcriptID"] = $transcriptID;

            $ck = DB::table('receipentdata')->where('paymentref', $req["paymentref"])->first();
            if(!$ck)
            {
                $res = (new SaveModel(new CompleteApplication(), $data))->execute();
                if($res)
                {
                    DB::table('applications')->where('transactionid',$req["paymentref"])->update(['isused'=>1]);
                    DB::table('u_g_student_accounts')->where('transactionid',$req["paymentref"])->update(['isused'=>1]);
                    $info =DB::table('applications as ap')
                    ->select('ap.matricno','ap.name','ap.programme','ap.email','ap.phone','rs.paymentref', 'rs.created_at',
                     'ap.category','rs.paymentref','rs.organization','rs.email as remail','rs.phone as rphone','rs.address1','rs.address2')
                    ->join('receipentdata as rs','ap.transactionID','=','rs.paymentref')
                    ->where('rs.paymentref', $req["paymentref"])
                    ->first();

                    $this->SendTranscriptEmail($u->email, $transcriptID,$req["names"]);
                    return  response(['message' => 'Recorded Created Successfully','statuscode'=>0,'info'=>$info]);
                }
            }
            if($ck)   return  response(['message' => 'TransactionID Already Used','statuscode'=>1]);
    }
    public function deleteApplication($guid)
    {
        if($guid)
        {
            $del = DB::table('applications')->where('guid',$guid)->delete();
            if($del) return  response(['message' => 'Record Deleted Successfully','statuscode'=>0]);
            if(!$del) return  response(['message' => 'Record Deletion Failed','statuscode'=>1]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function makePayments(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],   'guid' => ['required', 'string'],

        ]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }

        $matricno = $req["matricno"];
        $u = DB::table('users')->where('matricno', $matricno)->first();
        $pre = "TR";
        $pred = date('y')."APP";
        $transID = $pre.$pred.strtoupper($this->randomPassword()).substr(mt_rand(1111, 99999999) . mt_rand(1111, 9999) . mt_rand(1111, 9999), 1, 3);
        $guid = $req["guid"];
              $counrty = $req["country"];
              $state   = $req["state"];
             // $co = DB::SELECT('CALL GetCourrierByState(?)',array($state));
               if($counrty=='161')
                {
                    $p = DB::SELECT('CALL GetLocalPayment(?)', array($state));
                    $id =$p[0]->productID;
                    $amt =$p[0]->amount;
                }
                else
                {
                    $p = DB::SELECT('CALL GetForeignPayment(?)', array($counrty));
                    $id =$p[0]->productID;
                    $amt =$p[0]->amount;
                    # code...
                }
          //create payment records
          $datas["matricno"]      =   $matricno;
          $datas["transactionID"] =   $transID;
          $datas["description"]   =   "Transcript Application";
          $datas["amount"]        =    $this->getProductAmount($id);
          $datas["ispaid"]        =    false;
          $datas["url"]           =    $guid;
          $datas["status"]        =    true;
          $datas["isused"]        =    false;
          $datas["productID"]     =    $id;
          $datas["response"]      =    "Pending";
          $datas["apptype"]       =    "TR";
          $pay = (new SaveModel(new PaymentRecord(), $datas))->execute();
          if($pay)
          {
                //Previous Application Information
                        DB::table('applications')->where('guid',$guid)->update(['transactionID'=>$transID]);
                        $url = config('baseUrl.make_request_url');
                        $parameters = array(
                            "product_id" =>$id,
                            "trans_ref" => $transID,
                            "user_email" =>  $u->email,
                            "user_number" => $matricno,
                            "user_number_desc" => $u->name,
                            "returning_url" => config('baseUrl.returning_url')
                        );

                        $p = http_build_query($parameters);
                        $curl = curl_init($url);
                        curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                        curl_setopt($curl, CURLOPT_POST, 1);
                        curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
                        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                        $headers = array(
                            "token: funda123",
                        );
                        $this->SaveLogggers(json_encode($parameters));
                        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                        $resp = curl_exec($curl);
                        curl_close($curl);
                        $res = json_decode($resp);

                        $this->SaveLogggers(json_encode($res));
                        DB::table('u_g_student_accounts')->where('transactionID',$res->trans_ref)->update(['trans_id'=>$res->trans_id]);
                        return $resp;

                        return  response(['message' => 'Recorded Created Successfully','statuscode'=>0]);
             }


    }
    public function getOneApplicantInfo($guid)
    {
        $data = DB::SELECT("CALL GetOneApplicant(?)", array($guid)); //DB::table('applications')->where('guid', $guid)->first();
        if($data)
        {
            return  response([
                'affectedRows' =>1,
                'data'=>$data,
                'state'=>$data[0]->state,
                'programme'=>$data[0]->programme,
                'country'=>$data[0]->country,
                'countryid'=>$data[0]->countryid,
                'state'=>$data[0]->state,
                'statuscode'=>0
                ]);
        }
        return $guid;
    }
    public function returnCall()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {


            $txnref = request()->input('txnref');
            $status = request()->input('status');
            // Sanitize and validate inputs
            $txnref = filter_var($txnref, FILTER_SANITIZE_STRING);
            $status = filter_var($status, FILTER_SANITIZE_STRING);

            if (!isset($txnref)) {
                // Handle the case where txnref is not set
                return redirect()->away(env('APP_FRONTEND_URL_START'));
            }

            $updateData = ['response' => $status];

            if ($status === "Transaction Successful"  ||  $status === "Approved Successful"  ||  $status === "Approved by Financial Institution") {
                 //$updateData['ispaid'] = 1;
                 $up = DB::table('u_g_student_accounts')->where('transactionID', $txnref )->update(['ispaid'=>1, 'response'=>$status]);
                 $redirectUrl = "https://transcript1.lautech.edu.ng/myapp/#/admin/success";  //env('APP_FRONTEND_URL_SUCCESS');
            } else {
                $redirectUrl =  "https://transcript1.lautech.edu.ng/myapp/#/admin/failed"; //env('APP_FRONTEND_URL_FAILED');
            }

            // Use Eloquent for database operations
            DB::table('u_g_student_accounts')->where('transactionID', $txnref)->update($updateData);

            return redirect()->away($redirectUrl);
        }

        return redirect()->away('https://transcript1.lautech.edu.ng/myapp/#/adminfailed');
    }

    public function getProductAmount($pid)
    {
        $amt = DB::table('applicationslist')->where('productID', $pid)->first();
        if($amt) return $amt->amount;
        return $pid;
    }
    public function addApplication(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],  'email' => ['required', 'string'],  'phone' => ['required', 'string'],  'category' => ['required', 'string'],
         'programme' => ['required', 'string'],  'state' => ['required', 'string'],'country' => ['required'],'name' => ['required'],
        ]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }
        $name = $req["name"];
        $pre = "TR";
        $pred = date('y')."APP";
        $transID = $pre.$pred.strtoupper($this->randomPassword()).substr(mt_rand(1111, 99999999) . mt_rand(1111, 9999) . mt_rand(1111, 9999), 1, 3);

        $guid = Str::uuid().Str::uuid();
        $category = $req["category"];


        $data["matricno"] =   $req["matricno"];
        $data["email"] =      $req["email"];
        $data["phone"] =      $req["phone"];
        $data["programme"] =  $req["programme"];
        $data["state"] =      $req["state"];
        $data["country"] =    $req["country"];
        $data["name"] =       $name;
        $data["category"] =   $category;
        $data["guid"] =       $guid;
        $data["trackID"] =    Str::uuid();
        $data["transactionID"] =$transID;

        $ck = DB::table('applications')->where('transactionID', $transID)->first();

        if(!$ck)
        {
            $coamt =0;
            $amt =0; $id =0;

            //Create applications
            $res =  (new SaveModel(new TranscriptApplication(), $data))->execute();

            if($res)
            {
                  $counrty = $req["country"];
                  $state   = $req["state"];
                  $co = DB::SELECT('CALL GetCourrierByState(?)',array($state));

                  if($category=='Softcopy')
                  {
                    $id =351;
                  }
                  else
                  {

                        if($counrty=='161')
                        {
                            $p = DB::SELECT('CALL GetLocalPayment(?)', array($state));
                            $id =$p[0]->productID;
                            $amt =$p[0]->amount;
                        }
                        else
                        {
                            $p = DB::SELECT('CALL GetForeignPayment(?)', array($counrty));
                            $id =$p[0]->productID;
                            $amt =$p[0]->amount;
                            # code...
                        }
                  }
              //create payment records
              $datas["matricno"]      =   $req["matricno"];
              $datas["transactionID"] =   $transID;
              $datas["description"]   =   "Transcript Application";
              $datas["amount"]        =    $this->getProductAmount($id);
              $datas["ispaid"]        =    false;
              $datas["url"]           =    $guid;
              $datas["status"]        =    true;
              $datas["isused"]        =    false;
              $datas["productID"]     =    $id;
              $data["category"]       =    $req["category"];
              $datas["response"]      =    "Pending";
              $datas["apptype"]       =    "TR";
              $pay = (new SaveModel(new PaymentRecord(), $datas))->execute();
              if($pay)
              {
                            $url = "https://icomess.lautech.edu.ng/index.php/api/requestNew/"; //config('baseUrl.make_request_url');
                            $returning_url ="https://transcript1.lautech.edu.ng/api/v1/returncall" ;
                            $parameters = array(
                                "product_id" =>$id,
                                "trans_ref" => $transID,
                                "user_email" =>  $req["email"],
                                "user_number" => $req["matricno"],
                                "user_number_desc" => "Full Name",
                                "returning_url" =>  $returning_url
                            );
//config('baseUrl.returning_url') .$guid
                            $p = http_build_query($parameters);
                            $curl = curl_init($url);
                            curl_setopt($curl, CURLOPT_URL, $url);
                            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($curl, CURLOPT_POST, 1);
                            curl_setopt($curl, CURLOPT_POSTFIELDS, $p);
                            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                            $headers = array(
                                "token: funda123",
                            );
                            $this->SaveLogggers(json_encode($parameters));
                            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                            $resp = curl_exec($curl);
                            curl_close($curl);
                            $res = json_decode($resp);

                            $this->SaveLogggers(json_encode($res));
                            DB::table('u_g_student_accounts')->where('transactionID',$res->trans_ref)->update(['trans_id'=>$res->trans_id]);
                            return $resp;

                            return  response(['message' => 'Recorded Created Successfully','statuscode'=>0]);
                 }

            }
            else
            {
                return  response([
                    'affectedRows' =>0,
                    'data'=>0,
                    'statuscode'=>1
                    ]);
            }
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }

    }
    public function getStates($cid)
    {
        $data = DB::table('worldstates')->where('countryid', $cid)->get();
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function programmes()
    {
        $data = DB::table('department')->orderby('department','asc')->get();
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function country()
    {
        $data = DB::table('countrystate')->get();
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function getAllPaymentsAttempts(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],
        ]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }

        $matricno = $request->matricno;
        $data = DB::SELECT('CALL FetchPaymentAttempts(?)', array($matricno));
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function getAllPaymentsPaid(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],
        ]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }

        $matricno = $request->matricno;
        $data = DB::SELECT('CALL FetchPaidAttempts(?)', array($matricno));
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function fetchTranscriptInformation(Request $request)
    {
        $req = $request->all();
        $validation = Validator::make($req, [
         'matricno' => ['required', 'string'],
        ]);

        if ($validation->fails()){
            return response()->json(['error'=>$validation->errors()],422);
        }

        $matricno = $request->matricno;
        $data = DB::SELECT('CALL FetchTranscriptRecord(?)', array($matricno));
        if(count($data) > 0)
        {
            return  response([
                'affectedRows' =>count($data),
                'data'=>$data,
                'statuscode'=>0
                ]);
        }
        else
        {
            return  response([
                'affectedRows' =>0,
                'data'=>0,
                'statuscode'=>1
                ]);
        }
    }
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();
        return  response([
            'message' => 'Successfully logged out'
        ]);
    }
    public function getRegistration(Request $request)
    {
                //  $da = $request->session()->all();
                //  $request->session()->forget('da');
                Auth::logout();
                // $request->session()->flush();
                $req = $request->all();
                $islogin =true;
                $validation = Validator::make($req, [
                 'matricno' => ['required', 'string'],
                ]);

                  if ($validation->fails()){
                    return response()->json(['error'=>$validation->errors()],422);
                  }

             //$data  = DB::table('studentinformation')->where('Matric',$req["matricno"])->first();
             $check = DB::table('users')->where('matricno', $req["matricno"])->first();


                if($check)
                {

                         if(Auth::attempt(['email' => $check->email, 'password' => $check->matricno]))
                         {

                             //return $check;
                             //$u = User::where('email',$check->email)->first();
                             //$token = $user->createToken('transcriptApp')->accessToken;

                             return response()->json(['affectedRows' =>1,
                                     'data'=>$check,
                                     //'token'=>$token,
                                     'matricno'=>$check->matricno,
                                     'statuscode'=>0
                             ]);
                         }
                         else
                         {

                            DB::table('users')->where('matricno', $check->matricno)->delete();
                            return  response()->json([
                                 'affectedRows' =>0,
                                 'message'=>'Authenticated Failed',
                                 'data'=>0,
                                 'statuscode'=>1
                                 ]);
                         }
               }
                else
                {
                            return  response()->json([
                                'affectedRows' =>0,
                                'data'=>0,
                                'statuscode'=>1
                                ]);
                }





    }
    public function Login(Request $request)
    {

        $user = User::where('email', $request->email)->first();
        if (!$user || !Hash::check($request->password, $user->password))
        {
            return response()->json([
                'message' => 'Invalid',
                'error' =>[
                    'password' =>'The password do not match'
                ]
                ], 422);
        }

     return response()->json([
        'access_token' => $user->createToken('api-token')->plainTextToken,
        'type'=>'bearer',
     ],200);
    }
    // public function index()
    // {
    //     //HaHasFactory protected $fillable =
    //     return TranscriptApplicationResource::collection(TranscriptApplication::all());
    // }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreTranscriptApplicationRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(TranscriptApplicationRequest $request)
    {
        $u = User::where('email', $request->email)->first();
        $m = User::where('matricno', $request->matricno)->first();
        if($request)
        {
                if(!$u && !$m)
                {
                    $u=  User::create([
                            'email' => $request->email,
                            'password' => Hash::make($request->password),
                            'phone'=> $request->phone,
                            'matricno'=> $request->matricno,
                            'name' => $request->surname. ' '.$request->othername,
                            'guid'=> Str::uuid()
                        ]);

                }
              $t=   TranscriptApplication::create([
                    'email' => $request->email,
                    'phone'=> $request->phone,
                    'matricno'=> $request->matricno,
                    'name' => $request->surname. ' '.$request->othername,
                    'programme' => $request->programme,
                    'state' => $request->state,
                    'country' => $request->country,
                    'guid'=> Str::uuid()
                ]);
            return response()->json($t, 201);
        }
        else
        {
            $msg="Error Occured While Processing, Please try again";
            return response()->json($msg, 401);
        }

    }

    // public function Logout(Request $request)
    // {
    //     $request->user()->currentAccessToken->delete();
    //     return response()->json([
    //         'message' =>'Logged out Successfully',
    //     ]);
    // }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TranscriptApplication  $transcriptApplication
     * @return \Illuminate\Http\Response
     */
    public function show(TranscriptApplication $transcriptApplication)
    {
        //
        return new TranscriptApplicationResource($transcriptApplication);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TranscriptApplication  $transcriptApplication
     * @return \Illuminate\Http\Response
     */
    public function edit(TranscriptApplication $transcriptApplication)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateTranscriptApplicationRequest  $request
     * @param  \App\Models\TranscriptApplication  $transcriptApplication
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateTranscriptApplicationRequest $request, TranscriptApplication $transcriptApplication)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TranscriptApplication  $transcriptApplication
     * @return \Illuminate\Http\Response
     */
    public function destroy(TranscriptApplication $transcriptApplication)
    {
        //
    }

    public function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyz1234567890';
        $pass = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 5; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        //return implode($pass); //turn the array into a string
        return implode($pass);
    }

    public function SaveLogggers($request)
    {
        $dat['request']             =  $request;
        $res =(new SaveModel(new RequestLogger(), $dat))->execute();
    }

    public function SendEmail($email)
    {
          $url ="https://transcript1.lautech.edu.ng/TrackingNow";
          $parameters =
                             '{
                                "bounce_address": "bounced@bounce.mailx.lautech.edu.ng",
                                "from": { "address": "appman@mailx.lautech.edu.ng","name": "LAUTECH Transcript Progress Tracking" },
                                "to": [ { "email_address": { "address": "'.$email.'", "name": "'.Auth::user()->name.'" }}],
                                "reply_to":[{"address": "webmaster@lautech.edu.ng","name": "LAUTECH Webmaster"}],
                                "subject": "LAUTECH Transcript Progress Tracking",
                                "textbody": "LAUTECH Transcript Progress Tracking:",
                                "htmlbody": "<html><body>Dear Sir/Ma, '.strtoupper(Auth::user()->name ).'
                                Thank you for completing your request for LAUTECH academic transcript. Pls use the link below to track the progress of your application.
                                If you need further clarification please send email to. <img src=\"cid:img-welcome-design\"> <img src=\"cid:img-CTA\"><h1><a href=\"'.$url.'\">Track Transcript</a></h1></body></html>",
                             }';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL,"https://api.zeptomail.com/v1.1/email");
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);  //Post Fields
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $headers = array();
                        $headers[] = 'Accept: application/json';
                        $headers[] = 'Content-Type: application/json';
                        $headers[] = 'Authorization:Zoho-enczapikey wSsVR60k+R74Wv11nDOuI+hpyl1UBlv0HEl90FTy4nb1GaiT9sc+xhCaDQX1T/QfFWM4RTEWpLkukB9U2jdc290sw18FDyiF9mqRe1U4J3x17qnvhDzOXW5YkhKBL4gOxgponWhpEMEk+g==';
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                        $server_output = curl_exec ($ch);
    }

    public function SendTranscriptEmail($email,$tid,$name)
    {
        $url="https://transcript1.lautech.edu.ng/GetTranscriptTrackingStatus/".$tid;
        $parameters =
        '{
        "bounce_address": "bounced@bounce.mailx.lautech.edu.ng",
        "from": { "address": "appman@mailx.lautech.edu.ng","name": "LAUTECH Transcript Application" },
        "to": [ { "email_address": { "address": "'. $email.'", "name": "'.$name.'" }}],
        "reply_to":[{"address": "webmaster@lautech.edu.ng","name": "LAUTECH Webmaster"}],
        "subject": "Transcript Application Confirmation",
        "textbody": "Transcript Application",
        "htmlbody": "<html><body>Dear Sir/Ma, '.strtoupper($name).' Congratulations!!!, Your have successfully submitted Application. Please track your transcript status using this link '. $url.'</h1></body></html>",
      }';

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL,"https://api.zeptomail.com/v1.1/email");
      curl_setopt($ch, CURLOPT_POST, 1);
      curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);  //Post Fields
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      $headers = array();
      $headers[] = 'Accept: application/json';
      $headers[] = 'Content-Type: application/json';
      $headers[] = 'Authorization:Zoho-enczapikey wSsVR60k+R74Wv11nDOuI+hpyl1UBlv0HEl90FTy4nb1GaiT9sc+xhCaDQX1T/QfFWM4RTEWpLkukB9U2jdc290sw18FDyiF9mqRe1U4J3x17qnvhDzOXW5YkhKBL4gOxgponWhpEMEk+g==';
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $server_output = curl_exec ($ch);
     // var_dump($server_output);
     }
}
