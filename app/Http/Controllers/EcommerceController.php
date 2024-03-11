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
use App\Models\Cart;
use App\Models\Comment;

class EcommerceController extends Controller
{
    public function seachProductByCategory(Request $request){
        try{
            $rData=$request->only(['name']);
            $validator=[
                'name' => ['required','exists:categories,name'],
            ];
            $validationMessages = [
                'name.required' => "Le nom de la catégorie est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $name =  $rData['name'];
            $idCategory = Category::where('name',$name)->first();
            $productFound = Product::where('category_id', $idCategory->id )->with('comments','category','images_products')->get();
            if ($productFound-> isEmpty()){
                return [];
            }

            $formattedProducts = $productFound->map(function ($product) {
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

            return response()->json(
                $formattedProducts

            , 200);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de l'affichage  des produit. Veuillez réessayer",
                ],400
                );
        }
    }


    public function addCart(Request $request){
        try{
            $rData=$request->only(['id','product', 'user', 'qte']);
            $validator=[
                'product' => ['required','exists:products,id'],
                'user' => ['required', 'exists:users,id'],
                'qte' => ['required'],

            ];
            $validationMessages = [
                'product.required' => "Le nom de produit est requis",
                'user.required' => "La reference de l'utilisateur  est requise",
                'qte.required' => "Le quantité  du produit est requise",


            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $prod =  $rData['product'];
            $user =  $rData['user'];
            $qte =  $rData['qte'];


            $cart = new Cart();
            $cart->id = Uuid::uuid4()->toString();
            $cart->product_id =  $prod ;
            $cart->user_id =  $user ;
            $cart->qte =  $qte ;
            $cart->is_paid= false;
            $cart->save();

            return response()->json([
                "success" => true,
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors de lajout du produit au panier. Veuillez réessayer",
                ],400
                );
        }
    }

    public function paidProduct(Request $request){
        try{
            $rData=$request->only(['id','user']);
            $validator=[
                'product_id'=> ['required','exists:products,id'],
                'user'=> ['required','exists:users,id'],
            ];
            $validationMessages = [
                'product_id.required' => "La reference du produit est requise",
                'user.required' => "La reference de l'utilisateur est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $id =  $rData['product_id'];
            $user =  $rData['user'];
            $cart = Cart::where('user_id', $user)->where('product_id', $id)->first();
            $qte = $cart->qte;


            $product = Product::where('id',$id)->first();
            $product->available_stock -=  $qte;
            $product ->is_paid = true;
            $product ->save();



            return response()->json([
                "success" => true,
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du paiement. Veuillez réessayer",
                ],400
                );
        }
    }

    public function getProductInCart(Request $request){
        try{
            $rData=$request->only(['user']);
            $validator=[
                'user'=> ['required','exists:users,id'],
            ];
            $validationMessages = [
                'user.required' => "La reference de l'utilisateur est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $user =  $rData['user'];


            $productsInCart = Cart::where('user_id',$user)->where('is_paid', false)->with('product')->get();
            if ($productsInCart ->isEmpty()){
                return [];
            }

            $products = $productsInCart->pluck('product');


            return response()->json(
                $products
            , 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du paiement. Veuillez réessayer",
                ],400
                );
        }
    }




    public function retrieveProduct(Request $request){
        try{
            $rData=$request->only(['user', 'product']);
            $validator=[
                'user'=> ['required'],
                'product'=> ['required'],
            ];
            $validationMessages = [
                'user.required' => "La reference le l'utilisateur est requis",
                'product.required' => "La reference du produit est requis",


            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $user =  $rData['user'];
            $product =  $rData['product'];
            $deleted = Cart::where('user_id',$user)->where('product_id', $product)->delete();
            if ($deleted) {
                return response()->json([
                    "success" => true,
                    "message" => "Le produit a été retiré du panier avec succès.",
                ], 200);
            } else {
                return response()->json([
                    "success" => false,
                    "message" => "Le produit n'a pas été trouvé dans le panier de l'utilisateur.",
                ], 404);
            }

            return response()->json([
                "success" => true,
            ], 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue. Veuillez réessayer",
                ],400
                );
        }
    }


    public function getProductPaid(Request $request){
        try{
            $rData=$request->only(['user']);
            $validator=[
                'user'=> ['required','exists:users,id'],
            ];
            $validationMessages = [
                'user.required' => "La reference de l'utilisateur est requise",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $user =  $rData['user'];


            $productsInCart = Cart::where('user_id',$user)->where('is_paid', true)->with('product')->get();
            if ($productsInCart ->isEmpty()){
                return [];
            }
            $products = $productsInCart->pluck('product');



            return response()->json(
                $products
            , 201);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue lors du paiement. Veuillez réessayer",
                ],400
                );
        }
    }


    public function addComment(Request $request){
        try{
            $rData=$request->only(['id']);
            $validator=[
                'product'=> ['required','exists:products,id'],
                'user'=> ['required','exists:users,id'],
                'comment'=> ['required'],


            ];
            $validationMessages = [
                'user.required' => "La reference de l'utilisateur est requise",
                'product.required' => "La reference du produit est requise",
                'comment.required' => "Le commentaire à mettre est requis",
            ];
            $validatorResult=Validator::make( $rData, $validator, $validationMessages);

            if ($validatorResult->fails()) {
                return response()->json([
                    'message' => $validatorResult->errors()->first(),
                ], 400);
            }
            $product =  $rData['product'];
            $user =  $rData['user'];
            $product =  $rData['product'];

            $productPaid = Cart::where('product_id',$product)->where('user_id',$user)->where('is_paid',true)->first();
            if ($productPaid){
                $comment = new Comment();
                $comment->user_id = $user;
                $comment->product = $product;
                $comment->comment = $comment;
                $comment->save();
                return response()->json([
                    "success" => true,
                    "message" => "Votre commmentaire a eté ajouté avec succès.",
                ], 201);



            }
            else{
                return response()->json([
                    "success" => false,
                    "message" => "Impossibe d'éffectuer cette action.",
                ], 201);

            }





        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue. Veuillez réessayer",
                ],400
                );
        }
    }

    public function detailProduct(Request $request){
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


            $productFound = Product::where('id',$id)->with('comments','category','images_products')->get();

            $formattedProducts = $productFound->map(function ($product) {
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

            return response()->json(
                $formattedProducts
            , 200);

        }catch(Exception $ex){
            log::error($ex->getMessage());
            return response()->json(
                [
                    "message"=> "Une erreur est survenue. Veuillez réessayer",
                ],400
                );
        }
    }







}







