<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
//use App\Article;
use Illuminate\Support\Str;
use App\models\Ldapuser as ldap_user;


class LdapController extends Controller
{
    public function login(Request $request){
        $resp = array();
        $company = "";
        $user_name = $request->input('user_name');
        $password = $request->input('password');
        //dd($user_name);

        $company_ldap_url = "ldap.forumsys.com";
        //$ldap_dn = "cn=read-only-admin,dc=example,dc=com";
        //$user_name = "newton";
        //$password = "password";
        $ldap_dn = "uid=".$user_name.",dc=example,dc=com";
        $ldap_password = $password;

        $ldap_con = ldap_connect($company_ldap_url);
        // return response()->json(array('success'=>true, 200));
        ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
        if(@ldap_bind($ldap_con,$ldap_dn,$ldap_password)){
            $filter = "(uid=$user_name)";
            $result = ldap_search($ldap_con,"dc=example,dc=com",$filter);

            if(!$result){
                $message = "Unable to Search";
                $resp['status'] = 0;
                $resp['message'] = $message;
                //echo $message;
                return response()->json($resp, 201);
            }
            else{
                $api_token =  Str::random(60);
                $details = ldap_get_entries($ldap_con, $result);
                $ldap_user = ldap_user::updateOrCreate(
                    ['name' => $user_name,
                     'accepted_terms_conditions' => '1'
                     /*'employee_id' => '111',
                     'location' => 'mumbai',
                     'status' => 'active',
                     'profile_image'=>'profile_image.jpg',
                     'about_me'=>'this is about me text',
                     'phone' => '0987654321',
                     'blood_group'=>'b positive',
                     'background_image'=>'background_image.png',
                     'email'=>'newton@aaa.com'*/
                ],
                    ['api_token' => $api_token]
                );
            }
            $ldap_user = ldap_user::where('name',$user_name)->where('api_token',$api_token)->first();
            $message = "Success";
            $resp['status'] = 1;
            // $resp['api_token'] = $api_token;
            // $resp['details'] =  $details;
            $resp['user_details'] =  $ldap_user;
            return response()->json($resp, 200);
        }
        else{
            $message = "Login Failed";
            $resp['status'] = 0;
            $resp['message'] = $message;
            return response()->json($resp, 201);
        }
    }

    // public function get_all_users(){
    //     $ldap_dn = "cn=read-only-admin,dc=example,dc=com";
    //     $ldap_password = "password";
    //     $ldap_con = ldap_connect("ldap.forumsys.com");
    //     ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
    //     $resp =array();
    //     if(ldap_bind($ldap_con, $ldap_dn, $ldap_password)) {
    //         //$filter = "(uid=newton)";
    //         $filter = "(uid=*)";
    //         $result = ldap_search($ldap_con,"dc=example,dc=com",$filter);
    //         if(!$result){
    //             $message = "Unable to Search";
    //             $resp['status'] = 0;
    //             $resp['message'] = $message;
    //             //echo $message;
    //             return response()->json($resp, 201);
    //         }
    //         $entries = ldap_get_entries($ldap_con, $result);
    //         $resp['status'] = 1;
    //         $resp['message'] = $message;
    //         $resp['entries'] = $entries;
    //         return response()->json($resp, 200);
    //         //echo "<pre>";
    //         //print_r($entries);
    //         //echo "</pre>";
    //     } else {
    //         echo "Invalid user/pass or other errors!";
    //     }
    // }

    // public function index()
    // {
    //     //return Article::all();

    //     $ldap_dn = "cn=read-only-admin,dc=example,dc=com";
    //     $ldap_password = "password";
    //     $ldap_con = ldap_connect("ldap.forumsys.com");
    //     ldap_set_option($ldap_con, LDAP_OPT_PROTOCOL_VERSION, 3);
    //     if(ldap_bind($ldap_con, $ldap_dn, $ldap_password)) {
    //       echo "Bind successful!";
    //     } else {
    //         echo "Invalid user/pass or other errors!";
    //     }
    // }

    // public function show(Article $article)
    // {
    //     return $article;
    // }

    // public function store(Request $request)
    // {
    //     $article = Article::create($request->all());

    //     return response()->json($article, 201);
    // }

    // public function update(Request $request, Article $article)
    // {
    //     $article->update($request->all());

    //     return response()->json($article, 200);
    // }

    // public function delete(Article $article)
    // {
    //     $article->delete();

    //     return response()->json(null, 204);
    // }
}
