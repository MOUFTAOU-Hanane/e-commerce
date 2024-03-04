<?php
namespace App\Http\Controllers;

use App\Services\FileService;
use Illuminate\Support\Facades\Validator;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;


class AdminController extends Controller
{
    public function addProduct(Request $request){
        try{
            $rData=$request->only(['name', 'category','price', 'description',"number_in_stock",'images']);
            $validator=[
                'name' => ['required'],
                'price' => ['required'],
                'category' => ['required', 'exists:categories,id'],
                'description' => ['required'],
                'number_in_stock' => ['required','min:8'],
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
            $name =  $rData['name'];
            $price =  $rData['price'];
            $category =  $rData['category'];
            $desc =  $rData['description'];
            $number =  $rData['number_in_stock'];

            $product = new Product();
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
                "message" => "Le product a été enregistré avec succès.",
            ], 201);

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
            $rData=$request->only(['id','name', 'category','price', 'description',"number_in_stock",'images']);
            $validator=[
                'id'=> ['required','exists:products,id'],
                'name' => ['required'],
                'price' => ['required'],
                'category' => ['required', 'exists:categories,id'],
                'description' => ['required'],
                'number_in_stock' => ['required','min:8'],
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
            $name =  $rData['name'];
            $price =  $rData['price'];
            $category =  $rData['category'];
            $desc =  $rData['description'];
            $number =  $rData['number_in_stock'];
            $id =  $rData['id'];


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
                "message" => "Le product a été modifié avec succès.",
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la modification  d'un produit. Veuillez réessayer",
                ],400
                );
        }
    }

    public function deletedProduct(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'id'=> ['required','exists:products,id'],
            ];
            $validationMessages = [
                'id.required' => "La reference du produit est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id'];


            $product = Product::where('id',$id)->delete();



            return response()->json([
                "success" => true,
                "message" => "Le product a été supprimé avec succès.",
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la suppression  du produit. Veuillez réessayer",
                ],400
                );
        }
    }

    public function getProduct(Request $request){
        try{


            $products = Product::with('category')->all();

            return response()->json([
                $products
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du listing  des produits. Veuillez réessayer",
                ],400
                );
        }
    }

    public function addCategory(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'id'=> ['required'],
                'name'=> ['required'],
            ];
            $validationMessages = [
                'name.required' => "Le nom dela categorie de produit est requis",

            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $name =  $rData['name'];


            $category =  new Category();
            $category->id = Uuid::uuid4()->toString();
            $category->name =$rData['name'];
            $category->save();

            return response()->json([
                "success" => true,
                "message" => "La catégorie du produit a été créeé avec succès.",
            ], 201);

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


            $products = Category::all();

            return response()->json([
                $products
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du listing  des categories. Veuillez réessayer",
                ],400
                );
        }
    }


    public function deletedCategory(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'id'=> ['required','exists:categories,id'],
            ];
            $validationMessages = [
                'id.required' => "La reference du produit est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id'];


            $cateegoryFound = Category::where('id',$id)->delete();



            return response()->json([
                "success" => true,
                "message" => "La catégorie du produit a été supprimé avec succès.",
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de la suppression  du produit. Veuillez réessayer",
                ],400
                );
        }
    }

    public function updatedCategory(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'id'=> ['required','exists:categories,id'],
                'name'=> ['required'],
            ];
            $validationMessages = [
                'id.required' => "La reference du produit est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['id'];


            $categoryFound = Category::where('id',$id)->first();
            $categoryFound->name =$rData['name'];

            return response()->json([
                "success" => true,
                "message" => "La catégorie du produit a été modifié avec succès.",
            ], 201);

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







