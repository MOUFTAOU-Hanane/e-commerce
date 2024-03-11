<?php
namespace App\Http\Controllers;
use App\Services\FileService;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;
use App\Models\Product;
use App\Models\Category;
use App\Models\ImagesProduct;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

use function Laravel\Prompts\error;

class AdminController extends Controller
{
    public function addProduct(Request $request){
        try{
            $rData=$request->only(["users",'name', 'category','price', 'description',"number_in_stock",'images','user']);
            $validator=[
                'user' => ['required','exists:users,id'],
                'name' => ['required'],
                'price' => ['required'],
                'category' => ['required', 'exists:categories,id'],
                'description' => ['required'],
                'number_in_stock' => ['required'],
            ];
            $validationMessages = [

                'name.required' => "Le nom de produit est requis",
                'price.required' => "Le prix  du produit est est requis",
                'category.required' => "La catégorie du produit est requis",
                'category.exists' => "La catégorie du produit n'existe pas dans la base",
                'description.required' => "La description du produit est requis",
                'number_in_stock.required' => "Le nombre du produit disponible dans le stock est  requis",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $user =  $rData['user'];
            $name =  $rData['name'];
            $price =  $rData['price'];
            $category =  $rData['category'];
            $desc =  $rData['description'];
            $number =  $rData['number_in_stock'];
            $userFound = User::where("id",$user)->first();

            if($userFound -> role == 'admin'){
                $product = new Product();
                $product->name =  $name ;
                $product->price =  $price ;
                $product->category_id =  $category ;
                $product->description =  $desc ;
                $product->in_stock =  $number ;
                $product->available_stock =  $number ;
                $product->id = Uuid::uuid4()->toString();

                $product->save();

                return response()->json([
                    "success" => true,
                    "message" => "Le produIct a été enregistré avec succès.",
                ], 201);

                }


            return response()->json([
                "message" => "Vous n'avez pas de droit pour effectuer cette action",
            ],401 );

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de l'enregistrement  d'un produit. Veuillez réessayer",
                ],400
                );
        }
    }
    public function addImage(Request $request){
        try{
            $rData=$request->only(["user",'product', 'image']);
            $validator=[
                'user' => ['required','exists:users,id'],
                'product' => ['required', 'exists:products,id'],

            ];
            $validationMessages = [
                'product.required' => "La référence du produit est requis",
                'user.exists' => "La reference de l'utilisateur n'existe pas dans la base",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $user =  $rData['user'];
            $product =  $rData['product'];
            $userFound = User::where("id",$user)->first();

            if($userFound -> role == 'admin'){
                $image = new ImagesProduct();
                $image->product_id =  $product ;
                $image->id = Uuid::uuid4()->toString();



                if ($request->hasFile('image')) {
                    $imageFile = $request->file('image') ;
                    log::error( $imageFile );
                    $imageService = new FileService();
                    $imageUrl = $imageService->saveImage($imageFile);
                    $image->image =  $imageUrl ;
                    $image->save();

                return response()->json([
                    "success" => true,
                    "message" => "La photo de produit a été enregistré avec succès.",
                ], 201);




                 }else{
                    throw new Exception('Veuillez ajouté une image');
                 }



                }


            return response()->json([
                "message" => "Vous n'avez pas de droit pour effectuer cette action",
            ],401 );

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de l'enregistrement  d'un produit. Veuillez réessayer",
                ],400
                );
        }
    }


    public function updateProduct(Request $request){
        try{
            $rData=$request->only(['id','name', 'category','price', 'description',"number_in_stock",'images','user']);
            $validator=[
                'id'=> ['required','exists:products,id'],
                'name' => ['required'],
                'user' => ['required'],
                'price' => ['required'],
                'category' => ['required', 'exists:categories,id'],
                'description' => ['required'],
                'number_in_stock' => ['required','min:8'],
            ];
            $validationMessages = [
                'user.required' => "La reference de l'utilisateur est requise",
                'name.required' => "Le nom de produit est requis",
                'price.required' => "Le prix  du produit est est requis",
                'category.required' => "La catégorie du produit est requis",
                'category.exists' => "La catégorie du produit n'existe pas dans la base",
                'description.required' => "La description du produit est requis",
                'number_in_stock.required' => "Le nombre du produit disponible dans le stock est  requis",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $name =  $rData['name'];
            $price =  $rData['price'];
            $category =  $rData['category'];
            $desc =  $rData['description'];
            $number =  $rData['number_in_stock'];
            $id =  $rData['id'];

            $userFound = User::where("id",$id)->first();
            if($userFound -> role == 'admin'){

                $product = Product::where('id',$id)->first();
                $product->name =  $name ;
                $product->price =  $price ;
                $product->category =  $category ;
                $product->description =  $desc ;
                $product->number_in_stock =  $number ;
                $product->number =  $number ;
                $product->id = Uuid::uuid4()->toString();


                if ($request->hasFile('images')) {
                    $images = [];
                    foreach($request->file('images') as $imageFile){
                        $image = new FileService();
                        $image->saveImage($imageFile);
                        $images[] = $image;

                    } };

                $product->images =  json_encode($images) ;

                $product->save();

                return response()->json([
                    "success" => true,
                    "message" => "Le produit a été modifié avec succès.",
                ], 201);

                }
                else{
                    return response()->json([
                        "message" => "Vous n'avez pas de droit pour effectuer cette action",
                    ],401 );

                    }

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la modification  d'un produit. Veuillez réessayer",
                ],400
                );
        }
    }

    public function deleteProduct(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'user'=> ['required','exists:users,id'],
                'id'=> ['required','exists:products,id'],
            ];
            $validationMessages = [
                'id.required' => "La reference du produit est requise",
                'user.required' => "La reference de l'utilisateur est requise",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id'];
            $user =  $rData['user'];
            $userFound = User::where("id",$user)->first();

            if($userFound -> role == 'admin'){
                Product::where('id',$id)->delete();
                return response()->json([
                    "success" => true,
                    "message" => "Le product a été supprimé avec succès.",
                ], 201);
            }
            else{
                return response()->json([
                    "message" => "Vous n'avez pas de droit pour effectuer cette action",
                ],401 );

                }

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la suppression  du produit. Veuillez réessayer",
                ],400
                );
        }
    }


public function getProduct(Request $request)
{
    try {
        $products = Product::with('comments', 'category', 'images_products')->get();

        $formattedProducts = $products->map(function ($product) {
            $images = $product->images_products->pluck('image')->toArray();
            return [
                'id' => $product->id,
                'name' => $product->name,
                'description' => $product->description,
                'price' => $product->price,
                'in_stock' => $product->in_stock,
                'available_stock' => $product->available_stock,
                'images' => $images,
                'category' => $product->category,
                'comments' => $product->comments,
            ];
        });

        return response()->json($formattedProducts, 201);
    } catch (Exception $ex) {
        Log::error($ex->getMessage());
        return response()->json([
            "message" => "Une erreur est survenue lors du listing des produits. Veuillez réessayer",
        ], 400);
    }
}

public function getImageUrl($name) {
    try {
        $baseUrl ="http://127.0.0.1:8000/api/image/";
        $fileName = storage_path('app/uploads/' . $name);


        if (Storage::disk('local')->exists($baseUrl .  $name))  {

             return response()->file($fileName);
        }

        throw new Exception('Image non trouvée');
    } catch (\Exception $th) {
        throw $th;
    }}


    public function addCategory(Request $request){
        try{
            $rData=$request->only(['name','user']);
            $validator=[
                'name'=> ['required','unique:categories,name'],
                'user'=> ['required'],
            ];
            $validationMessages = [
                'name.required' => "Le nom de la catégorie de produit est requis",
                'user.required' => "La reference de l'utilisateur est requise",


            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $name =  $rData['name'];
            $user =  $rData['user'];


            $userFound = User::where("id",$user)->first();
            if($userFound -> role == 'admin'){

                $category =  new Category();
                $category->id = Uuid::uuid4()->toString();
                $category->name =$name;
                $category->save();

                return response()->json([
                    "success" => true,
                    "message" => "La catégorie du produit a été crée avec succès.",
                ], 201);
            }
            else{

                return response()->json([
                    "message" => "Vous n'avez pas de droit pour effectuer cette action",
                ],401 );

                }

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la creation de la catégorie  du produit. Veuillez réessayer",
                ],400
                );
        }
    }


    public function getCategory(Request $request){
        try{
            $categories = Category::all();

            return response()->json(
                $categories
            , 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du listing  des categories. Veuillez réessayer",
                ],400
                );
        }
    }


    public function deleteCategory(Request $request){
        try{
            $rData=$request->only(['id_category', 'user']);
            $validator=[
                'id_category'=> ['required','exists:categories,id'],
                'user'=> ['required','exists:users,id'],

            ];
            $validationMessages = [
                'id_category.required' => "La reference de la catégorie est requise",
                'user.required' => "La reference de l'utilisateur est requise",

            ];
            log::error($rData['user']);
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id_category'];
            $user = $rData['user'];

            $userFound = User::where("id",$user)->first();
            if($userFound -> role == 'admin'){
                Category::where('id',$id)->delete();

                return response()->json([
                        "success" => true,
                        "message" => "La catégorie du produit a été supprimé avec succès.",
                ], 200);
            }
            else{

                return response()->json([
                        "message" => "Vous n'avez pas de droit pour effectuer cette action",
                ],401 );

            }
        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la suppression  du produit. Veuillez réessayer",
                ],400
                );
        }
    }

    public function updateCategory(Request $request){
        try{
            $rData=$request->only(['id_category','name','user']);
            $validator=[
                'id_category'=> ['required','exists:categories,id'],
                'name'=> ['required'],
                'user'=> ['required','exists:users,id'],
            ];
            $validationMessages = [
                'id_category.required' => "La reference de la catégorie est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id_category'];
            $user =  $rData['user'];
            $userFound = User::where("id",$user)->first();
            if($userFound -> role == 'admin'){
                $categoryFound = Category::where('id',$id)->first();
                $categoryFound->name =$rData['name'];
                $categoryFound->save();

                return response()->json([
                    "success" => true,
                    "message" => "La catégorie du produit a été modifié avec succès.",
                ], 201);
            }
            else{

                return response()->json([
                        "message" => "Vous n'avez pas de droit pour effectuer cette action",
                ],401 );

            }

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la modification de la catégorie  du produit. Veuillez réessayer",
                ],400
                );
        }
    }

}







