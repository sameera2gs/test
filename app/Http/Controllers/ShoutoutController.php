<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Shoutouts as Shoutout;
use App\models\ShoutoutFiles;
use App\models\Ldapuser;
use App\models\department;
use Illuminate\Support\Facades\Validator;
use \Waavi\Sanitizer\Sanitizer;
use Illuminate\Support\Facades\DB;

class ShoutoutController extends Controller
{
    //
    public function __construct()
    {

    }

    public function addShoutout(Request $request){
        //$this->filterData($re);
        //die();
        // $ldap_user = Ldapuser::find(1);
        // dd($ldap_user->department->name);
        // die();
        // $department = department::find(1)->first();
        // echo $department->id;die;
        // dd($dept->ldap_users->toArray());

        $resp = array();
        $rules = $this->rules();
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $message = "Validation fails";
            $resp['status'] = 0;
            $resp['message'] = $message;
            $resp['errors'] = $errors;
            return response()->json($resp, 201);
            //print_r($validator->errors()); die();
        }
        else{
            $core_values_array = $request->input('core_values');
            $shoutout_photos = $request->input('shoutout_photos');

            $shoutout = new Shoutout;
            $shoutout->created_by = $request->input('created_by');//created_by is having login id(pk)
            $shoutout->shoutout_for_user = $request->input('shoutout_for_user');
            $shoutout->core_values = implode(', ', $core_values_array);
            $shoutout->shoutout_text = $request->input('shoutout_text');
            $ldap_user = Ldapuser::find($shoutout->shoutout_for_user);
            $department_id = $ldap_user->department_id;
            $shoutout->department = $department_id; //$this->get_department($shoutout->shoutout_for_user) ; // write function to get department of shoutout_for_user
            $shoutout->save();
            $inserted_id = $shoutout->id;
            $message = "Shoutout Inserted";
            $resp['status'] = 1;
            $resp['message'] = $message;
            $resp['id'] = $inserted_id;
            $photo_data =array();
            foreach ($shoutout_photos as $key => $value) {
                $photos = array('shoutout_id' => $inserted_id,
                                'file_id'=>$value['file_id'],
                                'file_type'=>$value['file_type'],
                                'created_at'=>date('Y-m-d H:i:s')
                          );
                $photo_data[] = $photos;
            }
            if($inserted_id){
                $message .= " ,Images Inserted";
                $resp['message'] = $message;
                ShoutoutFiles::insert($photo_data);
            }
            else{
                $message .= " ,Images are not Inserted";
                $resp['message'] = $message;
            }
            return response()->json($resp, 200);
        }
    }

    function editShoutout(Request $request){
        $resp = array();
        $rules = $this->rules();
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $errors = $validator->errors();
            $message = "Validation fails";
            $resp['status'] = 0;
            $resp['message'] = $message;
            $resp['errors'] = $errors;
            return response()->json($resp, 201);
            //print_r($validator->errors()); die();
        }
        else{
            $id = $request->id;
            $core_values_array = $request->core_values;
            $shoutout = Shoutout::find($id)->first();
            $shoutout->created_by = $request->shoutout_for_user;
            $shoutout->updated_at = date('Y-m-d H:i:s');
            $shoutout->shoutout_for_user = $request->shoutout_for_user;
            $shoutout->core_values = implode(', ', $core_values_array);
            $shoutout->shoutout_text = $request->shoutout_text;
            $shoutout->department = $request->department;
            $shoutout_photos = $request->shoutout_photos;

            $photo_data =array();
            $file_ids = array();

            foreach ($shoutout_photos as $key => $value) {
                $photos = array('shoutout_files_id' => $value['id'],
                                'shoutout_id' => $id,
                                'file_id'=>$value['file_id'],
                                'file_type'=>$value['file_type'],
                                'updated_at'=>date('Y-m-d H:i:s')
                          );
                $file_ids []= $value['id'];
                $photo_data[] = $photos;
            }

            //$ldap_user = Ldapuser::find(1);
            //$ldap_user->department->id;
            $files = ShoutoutFiles::select('id','file_type')->where('shoutout_id',$id)->get()->toArray();
            $current_files = array_column($files,'id');

            $files_to_be_add = array_diff($file_ids,$current_files);
            $files_to_be_del = array_diff($current_files,$file_ids);

            $shoutout->save();

            $this->remove_files($files_to_be_del);
            $add_photos = $this->add_files($files_to_be_add,$photo_data);
            if(!empty($add_photos))
            {
                ShoutoutFiles::insert($add_photos);
            }

            $message = "Shoutout Updated";
            $resp['status'] = 1;
            $resp['message'] = $message;
            return response()->json($resp, 200);
        }
    }

    function get_department($emp_id,$request){
        $shoutout_for_user = $request->shoutout_for_user;
        $ldap_user = Ldapuser::find($emp_id)->first();
        $ldap_user->department->id;
        //$department = Ldapuser->department;
        return $department;
    }

    function add_files($files_to_be_add,$shoutout_photos)
    {
        $photo_data =array();
        foreach ($shoutout_photos as $key => $value)
        {
            if(in_array($value['shoutout_files_id'], $files_to_be_add))
            {
                $photos = array('shoutout_id' => $value['shoutout_id'],
                                'file_id'=>$value['file_id'],
                                'file_type'=>$value['file_type']
                          );
                array_push($photo_data, $photos);
            }
        }
        return $photo_data;
    }

    /*function add_files(Request $request){
        $photo_data =array();
        foreach ($shoutout_photos as $key => $value) {
            $photos = array('shoutout_id' => $inserted_id,
                            'file_id'=>$value['file_id'],
                            'file_type'=>$value['file_type']
                      );
            $photo_data[] = $photos;
        }
    }*/

    function remove_files($file_ids){
        //DB::enableQueryLog();
        return DB::table('shoutout_files')->whereIn('id', $file_ids)->delete();
        //$laQuery = DB::getQueryLog();
        //DB::disableQueryLog();

    }

    public function messages()
    {
        return [
            'created_by.required' => 'Created By Field is required',
            'created_by.integer' => 'Created By Field should be integer',
            'shoutout_for_user.required'  => 'shoutout_for_user Field is required',
            'core_values_array.required' => 'core_values_array Field is required',
            'shoutout_text.required'  => 'shoutout_text Field is required',
        ];
    }

    function filterData($request){
        $data = array(
        'created_by' => $request->input('created_by'),
        'shoutout_for_user' => $request->input('shoutout_for_user'),
        'core_values' => $request->input('core_values'),
        'shoutout_text' => $request->input('shoutout_text'),
        //'shoutout_photos' = $request->input('shoutout_photos');
        );
        $filters = [
            'created_by'         =>  'digit',
            'shoutout_for_user'  =>  'digit',
            'core_values'        =>  'cast:array',
            'shoutout_text'      =>  'escape|strip_tags',
        ];

        $sanitizer  = new Sanitizer($data, $filters);
        var_dump($sanitizer->sanitize());
    }

    function rules(){
        return  array(
            'created_by' => 'required|integer',
            'shoutout_for_user' => 'required|integer',
            'core_values' => 'required|array',
            'shoutout_text' => 'required|max:500',
        );
    }

    function searchForId($id, $array) {
        foreach ($array as $key => $val) {
            if ($val['id'] === $id) {
                //return $key;
                return $val['file_type'];
            }
        }
        return null;
    }

    public function getList()
    {
        $shoutouts_data = Shoutout::all();

        $shoutout_array = array();
        $main_obj = new \stdClass();
        foreach ($shoutouts_data as $key => $shoutout)
        {
            $shoutout_obj = new \stdClass();
            $shoutout_obj->shoutout_id   = $shoutout->id;
            $shoutout_obj->created_at    = date('d-m-Y', strtotime($shoutout->created_at));
            $shoutout_obj->updated_at    = date('d-m-Y', strtotime($shoutout->updated_at));
            $shoutout_obj->shoutout_text = $shoutout->shoutout_text;
            $shoutout_obj->is_approved   = $shoutout->is_approved;
            $shoutout_obj->is_reported   = $shoutout->is_reported;

            // created by data
            $creater = Ldapuser::find($shoutout->created_by);
            $creater_obj = new \stdClass();
            if(!empty($creater)){
                $creater_obj->id            = $creater->id;
                $creater_obj->name          = $creater->name;
                $creater_obj->profile_image = $creater->profile_image;
            }
            $shoutout_obj->created_by       = $creater_obj;

            // department data
            $department = department::find($shoutout->department);
            $dept_obj = new \stdClass();
            if(!empty($department)){
                $dept_obj->id               = $department->id;
                $dept_obj->name             = $department->name;
                $shoutout_obj->department   = $dept_obj;
            }
            $shoutout_obj->hats_off         = $shoutout->hats_off;

            // shoutout_for_user data
            $shoutout_for_user = Ldapuser::find($shoutout->shoutout_for_user);
            $sh_u_obj = new \stdClass();
            if(!empty($shoutout_for_user)){
                $sh_u_obj->id               = $shoutout_for_user->id;
                $sh_u_obj->name             = $shoutout_for_user->name;
                $sh_u_obj->profile_image    = $shoutout_for_user->profile_image;
            }

            $shoutout_obj->shoutout_for_user= $sh_u_obj;
            $shoutout_obj->core_values      = $shoutout->core_values;
            $shoutout_obj->location         = $shoutout->location;

            array_push($shoutout_array, $shoutout_obj);
        }
        $main_obj->shoutout_list = $shoutout_array;
        // print_r($shoutout_array);
        echo json_encode($main_obj);
    }

    public function getComments(Request $request)
    {
        $shoutout_id    = $request->shoutout_id;
        // $user_id        = $request->user_id;

        $comments = DB::table('comments')
                        ->where('shoutout_id',$shoutout_id)
                        ->where('is_approved',1)
                        // ->where('user_id',$user_id)
                        ->where('comment_id',null)
                        ->get();

        $comments_array = array();
        $main_obj = new \stdClass();
        foreach ($comments as $key => $comment)
        {
            $com_obj = new \stdClass();
            $com_obj->id            = $comment->id;
            $com_obj->comment_text  = $comment->comment_text;

            $child_comments = DB::table('comments')
                ->where('shoutout_id',$shoutout_id)
                ->where('is_approved',1)
                ->where('comment_id',$comment->id)
                ->get();

            $child_array = array();
            foreach ($child_comments as $key => $child)
            {
                // print_r($child);
                $child_obj = new \stdClass();
                $child_obj->id           = $child->id;
                $child_obj->comment_text = $child->comment_text;
                array_push($child_array, $child_obj);

                $child_comments1 = DB::table('comments')
                    ->where('shoutout_id',$shoutout_id)
                    ->where('is_approved',1)
                    ->where('comment_id',$child->id)
                    ->get();

                $child_array1 = array();
                foreach ($child_comments1 as $key => $child1)
                {
                    $child_obj1 = new \stdClass();
                    $child_obj1->id           = $child1->id;
                    $child_obj1->comment_text = $child1->comment_text;
                    array_push($child_array1, $child_obj1);

                    $child_comments2 = DB::table('comments')
                        ->where('shoutout_id',$shoutout_id)
                        ->where('is_approved',1)
                        ->where('comment_id',$child1->id)
                        ->get();

                    $child_array2 = array();
                    foreach ($child_comments2 as $key => $child2)
                    {
                        $child_obj2 = new \stdClass();
                        $child_obj2->id           = $child2->id;
                        $child_obj2->comment_text = $child2->comment_text;
                        array_push($child_array2, $child_obj2);

                        $child_comments3 = DB::table('comments')
                            ->where('shoutout_id',$shoutout_id)
                            ->where('is_approved',1)
                            ->where('comment_id',$child2->id)
                            ->get();

                        $child_array3 = array();
                        foreach ($child_comments3 as $key => $child3)
                        {
                            $child_obj3 = new \stdClass();
                            $child_obj3->id           = $child3->id;
                            $child_obj3->comment_text = $child3->comment_text;
                            array_push($child_array3, $child_obj3);

                            $child_comments4 = DB::table('comments')
                                ->where('shoutout_id',$shoutout_id)
                                ->where('is_approved',1)
                                ->where('comment_id',$child3->id)
                                ->get();

                            $child_array4 = array();
                            foreach ($child_comments4 as $key => $child4)
                            {
                                $child_obj4 = new \stdClass();
                                $child_obj4->id           = $child4->id;
                                $child_obj4->comment_text = $child4->comment_text;
                                array_push($child_array4, $child_obj4);

                                $child_comments5 = DB::table('comments')
                                    ->where('shoutout_id',$shoutout_id)
                                    ->where('is_approved',1)
                                    ->where('comment_id',$child4->id)
                                    ->get();

                                $child_array5 = array();
                                foreach ($child_comments5 as $key => $child5)
                                {
                                    $child_obj5 = new \stdClass();
                                    $child_obj5->id           = $child5->id;
                                    $child_obj5->comment_text = $child5->comment_text;
                                    array_push($child_array5, $child_obj5);
                                }
                                $child_obj4->comments = $child_array5;
                            }
                            $child_obj3->comments = $child_array4;
                        }
                        $child_obj2->comments = $child_array3;
                    }
                    $child_obj1->comments = $child_array2;
                }
                $child_obj->comments     = $child_array1;
            }
            $com_obj->comments       = $child_array;
            array_push($comments_array, $com_obj);
        }
        $main_obj->comments = $comments_array;
        echo json_encode($main_obj);
    }
    
    public function getNotifications(Request $request)
    {
        $user_id        = $request->user_id;
        $ldap_user      = Ldapuser::find($user_id);
        $department_id  = $ldap_user->department_id;

        $shoutouts_data = Shoutout::where('department',$department_id)->get();

        $shoutout_array = array();
        $main_obj = new \stdClass();
        foreach ($shoutouts_data as $key => $shoutout)
        {
            $shoutout_obj = new \stdClass();
            $shoutout_obj->shoutout_id   = $shoutout->id;
            $shoutout_obj->created_at    = date('d-m-Y', strtotime($shoutout->created_at));
            $shoutout_obj->updated_at    = date('d-m-Y', strtotime($shoutout->updated_at));
            $shoutout_obj->shoutout_text = $shoutout->shoutout_text;
            $shoutout_obj->is_approved   = $shoutout->is_approved;
            $shoutout_obj->is_reported   = $shoutout->is_reported;

            // created by data
            $creater = Ldapuser::find($shoutout->created_by);
            $creater_obj = new \stdClass();
            if(!empty($creater)){
                $creater_obj->id            = $creater->id;
                $creater_obj->name          = $creater->name;
                $creater_obj->profile_image = $creater->profile_image;
            }
            $shoutout_obj->created_by       = $creater_obj;

            // department data
            $department = department::find($shoutout->department);
            $dept_obj = new \stdClass();
            if(!empty($department)){
                $dept_obj->id               = $department->id;
                $dept_obj->name             = $department->name;
                $shoutout_obj->department   = $dept_obj;
            }
            $shoutout_obj->hats_off         = $shoutout->hats_off;

            // shoutout_for_user data
            $shoutout_for_user = Ldapuser::find($shoutout->shoutout_for_user);
            $sh_u_obj = new \stdClass();
            if(!empty($shoutout_for_user)){
                $sh_u_obj->id               = $shoutout_for_user->id;
                $sh_u_obj->name             = $shoutout_for_user->name;
                $sh_u_obj->profile_image    = $shoutout_for_user->profile_image;
            }

            $shoutout_obj->shoutout_for_user= $sh_u_obj;
            $shoutout_obj->core_values      = $shoutout->core_values;
            $shoutout_obj->location         = $shoutout->location;

            array_push($shoutout_array, $shoutout_obj);
        }
        $main_obj->notifications = $shoutout_array;
        // print_r($shoutout_array);
        echo json_encode($main_obj);        
    }

    public function reportedShoutouts(Request $request)
    {
        $user_id        = $request->user_id;
        $ldap_user      = Ldapuser::find($user_id);
        $department_id  = $ldap_user->department_id;

        $shoutouts_data = Shoutout::where('department',$department_id)->where('is_reported',1)->get();

        $shoutout_array = array();
        $main_obj = new \stdClass();
        foreach ($shoutouts_data as $key => $shoutout)
        {
            $shoutout_obj = new \stdClass();
            $shoutout_obj->shoutout_id   = $shoutout->id;
            $shoutout_obj->created_at    = date('d-m-Y', strtotime($shoutout->created_at));
            $shoutout_obj->updated_at    = date('d-m-Y', strtotime($shoutout->updated_at));
            $shoutout_obj->shoutout_text = $shoutout->shoutout_text;
            $shoutout_obj->is_approved   = $shoutout->is_approved;
            $shoutout_obj->is_reported   = $shoutout->is_reported;

            // created by data
            $creater = Ldapuser::find($shoutout->created_by);
            $creater_obj = new \stdClass();
            if(!empty($creater)){
                $creater_obj->id            = $creater->id;
                $creater_obj->name          = $creater->name;
                $creater_obj->profile_image = $creater->profile_image;
            }
            $shoutout_obj->created_by       = $creater_obj;

            // department data
            $department = department::find($shoutout->department);
            $dept_obj = new \stdClass();
            if(!empty($department)){
                $dept_obj->id               = $department->id;
                $dept_obj->name             = $department->name;
                $shoutout_obj->department   = $dept_obj;
            }
            $shoutout_obj->hats_off         = $shoutout->hats_off;

            // shoutout_for_user data
            $shoutout_for_user = Ldapuser::find($shoutout->shoutout_for_user);
            $sh_u_obj = new \stdClass();
            if(!empty($shoutout_for_user)){
                $sh_u_obj->id               = $shoutout_for_user->id;
                $sh_u_obj->name             = $shoutout_for_user->name;
                $sh_u_obj->profile_image    = $shoutout_for_user->profile_image;
            }

            $shoutout_obj->shoutout_for_user= $sh_u_obj;
            $shoutout_obj->core_values      = $shoutout->core_values;
            $shoutout_obj->location         = $shoutout->location;

            array_push($shoutout_array, $shoutout_obj);
        }
        // print_r($shoutout_array);
        $main_obj->soutouts = $shoutout_array;
        echo json_encode($main_obj);        
    }

    public function reportedShoutoutComments(Request $request)
    {
        $shoutout_id = $request->shoutout_id;
        $report_comments  = DB::table('reported_shoutout')->where('shoutout_id',$shoutout_id)->get(); 

        $main_array = array();
        $main_obj = new \stdClass();
        foreach ($report_comments as $key => $comment)
        {
            $reporter = DB::table('ldap_users')
                        ->where('id',$comment->reported_by)
                        // ->where('status',1)
                        // ->orderBy('id','desc')
                        ->get();
            foreach ($reporter as $key => $rep)
            {
                $obj = new \stdClass();
                $obj->id            = $rep->id;
                $obj->name          = $rep->name;
                $obj->profile_image = $rep->profile_image;
            }
            $comment->reporters = $obj;
            array_push($main_array, $comment);
        }
        $main_obj->reports = $main_array;

        echo json_encode($main_obj);
    }








}//end of controller