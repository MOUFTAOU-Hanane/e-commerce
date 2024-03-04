<?php
namespace App\Http\Controllers;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;


class UserController extends Controller
{
    public function register(Request $request){
        try{
            $rData=$request->only(['first_name', 'last_name','email', 'role',"password",'phone_number','localisation']);
            $validator=[
                'first_name' => ['required'],
                'last_name' => ['required'],
                'email' => ['required', 'unique:users,email'],
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
            $user->adresse =  $adresse ;
            $user->email =  $email ;
            $user->role =  $role ;
            $user->password =  Hash::make($pwd) ;
            $user->phone =  $phone ;
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
                "adresse" => $userFound->reference,
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
            $rData=$request->only(['email', "password"]);
            $validator=[

                'email' => ['required','exist:users,email'],
                'password' => ['required','min:8'],

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



}








