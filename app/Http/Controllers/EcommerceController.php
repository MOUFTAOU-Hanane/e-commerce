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
            $productFound = Product::where('category', $idCategory->id )->with('comments','category')->get();
            if ($productFound-> isEmpty()){
                return [];
            }

            return response()->json([
                $productFound

            ], 201);

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
            $rData=$request->only(['id','product', 'user']);
            $validator=[
                'product' => ['required','exists:products,id'],
                'user' => ['required', 'exists:products,id'],
                'qte' => ['required'],

            ];
            $validationMessages = [
                'product.required' => "Le nom de produit est requis",
                'user.required' => "Le prix  du produit est est requis",
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
            $qte =  $rData['user'];


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


            $product = Product::where('id',$id)->first();
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


            $productsInCart = Cart::where('user',$user)->where('is_paid', false)->with('product')->get();
            if ($productsInCart ->isEmpty()){
                return [];
            }

            $products = $productsInCart->pluck('product');


            return response()->json([
                $products
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
            $deleted = Cart::where('user',$user)->where('product', $product)->delete();
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


            $productsInCart = Cart::where('user',$user)->where('is_paid', true)->with('product')->get();
            if ($productsInCart ->isEmpty()){
                return [];
            }
            $products = $productsInCart->pluck('product');



            return response()->json([
                $products
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

            $productPaid = Cart::where('product',$product)->where('user',$user)->where('is_paid',true)->first();
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


            $productFound = Product::where('id',$id)->with('category', 'comments')->get();

            return response()->json([
                $productFound
            ], 200);

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







