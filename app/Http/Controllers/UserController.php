<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Ramsey\Uuid\Uuid;



class UserController extends Controller
{
    public function register(Request $request){
        try{
            $rData=$request->only(['first_name', 'last_name','email', 'role',"password",'phone_number','localisation']);
            $validator=[
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'email'],
                'role' => ['required'],
                'password' => ['required','min:8'],
                'phone_number' => ['required', 'unique:users,phone_number'],
                'localisation' => ['required']
            ];
            $validationMessages = [
                'first_name.required' => "Le nom de l'utilisateur est requis",
                'last_name.required' => "Le prénom de l'utilisateur est requis",
                'localisation.required' => "L'adresse de l'utilisateur est requis",
                'email.required' => "L'adresse email de l'utilisateur est requis",
                'role.required' => "Le role de l'utilisateur est  requis",
                'password.required' => "Le mots de passe est requis",
                'email.unique' => 'Cette adresse email est déja utilisée','
                phone_number.unique' => "Ce numéro de téléphone est déjà utilisé." ,
                'password.min' => 'Le mot de passe doit contenir au moins huit caractères',
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $firstname =  $rData['first_name'];
            $lastname =  $rData['last_name'];
            $adresse =  $rData['localisation'];
            $email =  $rData['email'];
            $role =  $rData['role'];
            $pwd =  $rData['password'];
            $phone =  $rData['phone_number'];

            $user = new User();
            $user->id = Uuid::uuid4()->toString();
            $user->first_name =  $firstname ;
            $user->last_name =  $lastname ;
            $user->localisation =  $adresse ;
            $user->email =  $email ;
            $user->role =  $role ;
            $user->password =  Hash::make($pwd) ;
            $user->phone_number =  $phone ;
            $user->save();

            return response()->json([
                "success" => true,
                "message" => "L'utilisateur a été enregistré avec succès.",
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la création d'un utilisateur. Veuillez réessayer",
                ],400
                );
        }
    }

    public function login(Request $request){
        try{
            $rData=$request->only(['email', "password"]);
            $validator=[

                'email' => ['required','exists:users,email'],
                'password' => ['required'],

            ];
            $validationMessages = [
                'email.required' => "L'adresse email de l'utilisateur est requis",
                'password.required' => "Le mots de passe est requis",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }

            $email =  $rData['email'];
            $pwd =  $rData['password'];

            $userFound = User::where('email',$email )->first();
            $password = Hash::check($pwd, $userFound->password);


            if (!$password) {
                return response()->json(
                    [
                        "message"=> "Votre mot de passe est incorrect. Veuillez réessayer",
                    ],400
                    );


            }
            $userDetail =   array(
                "id" => $userFound->id,
                "first_name" => $userFound->first_name,
                "last_name" => $userFound->last_name,
                "email" => $userFound->email,
                "adresse" => $userFound->localisation,
                "phone" => $userFound->phone_number
            );

            return response()->json($userDetail, 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                   "success"=> false,
                    "message"=> "Une erreur est survenue lors du login. Veuillez réessayer",
                ],400
                );
        }
    }



    public function changePassword(Request $request){
        try{
            $rData=$request->only(['email', "new-password"]);
            $validator=[

                'email' => ['required','exists:users,email'],
                'new-password' => ['required','min:8'],

            ];
            $validationMessages = [
                'email.required' => "L'adresse email de l'utilisateur est requis",
                'new-password.required' => "Le mots de passe est requis",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }

            $email =  $rData['email'];
            $pwd =  $rData['new-password'];

            $userFound = User::where('email',$email )->first();
            $userFound ->password = Hash::make($pwd) ;
            $userFound ->save();
            return response()->json(
                [
                    "message"=> "Votre mots de passe été modifié avec succes",
                ],200
                );


        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                   "success"=> false,
                    "message"=> "Une erreur est survenue lors du de la modification. Veuillez réessayer",
                ],400
                );
        }
    }


    public function updateAccount(Request $request){
        try{
            $rData=$request->only(['id','first_name', 'last_name','email', 'role',"password",'phone_number','localisation']);
            $validator=[
                'id' => ['required',"exists:users,id"],
                'first_name' => ['nullable'],
                'last_name' => ['nullable'],
                'email' => ['nullable', 'email'],
                'phone_number' => ['nullable', 'unique:users,phone_number'],
                'localisation' => ['nullable']
            ];
            $validationMessages = [
                'email.unique' => 'Cette adresse email est déja utilisée','
                phone_number.unique' => "Ce numéro de téléphone est déjà utilisé." ,
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id'];
            $user = User::where('id', $id)->first();

            if (isset($rData['first_name'])){
                $firstname =  $rData['first_name'];
                $user->first_name =  $firstname ;


            }

            if (isset($rData['last_name'])){
                $lastname =  $rData['last_name'];
                $user->last_name =  $lastname ;


            }

            if (isset($rData['adresse'])){
                $adresse =  $rData['localisation'];
                $user->localisation =  $adresse ;


            }
            if (isset($rData['phone_number'])){
                $phone =  $rData['phone_number'];
                $user->phone_number =  $phone ;


            }
            if (isset($rData['email'])){
                $email =  $rData['email'];
                $user->email =  $email ;


            }

            $user->save();

            return response()->json([
                "success" => true,
                "message" => "Les informations de l'utilisateur ont été modifié avec succès.",
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la modification des informations de l'utilisateur. Veuillez réessayer",
                ],400
                );
        }
    }


    public function resetPassword(Request $request){
        try{
            $rData=$request->only(['id','old-password', "new-password"]);
            $validator=[

                'id' => ['required','exists:users,id'],
                'old-password' => ['required',],
                'new-password' => ['required','min:8'],

            ];
            $validationMessages = [
                'id.required' => "La reference de l'utilisateur est requis",
                'old-password.required' => "L'ancien mots de passe de l'utilisateur est requis",
                'new-password.required' => "Le nouveau mots de passe de l'utilisateur est requis",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }

            $old_pwd =  $rData['old-password'];
            $new_pwd =  $rData['new-password'];
            $id =  $rData['id'];

            $user = User::where('id',$id )->first();
            if (Hash::check( $old_pwd, $user->password)){
                $user->password = Hash::make($new_pwd) ;
                $user ->save();
                return response()->json(
                    [
                        "message"=> "Opération effectué avec succes",
                    ],200
                    );


            }
            {

                return response()->json(
                    [
                        "message"=> "L'ancien mots de passe n'est pas correct",
                    ],400
                    );
            }





        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                   "success"=> false,
                    "message"=> "Une erreur est survenue lors du de la modification. Veuillez réessayer",
                ],400
                );
        }
    }



}








