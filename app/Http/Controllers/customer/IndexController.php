<?php

namespace App\Http\Controllers\customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Products_filter;
use App\Models\Section;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
// use Illuminate\Support\Str;

class IndexController extends Controller
{
    public function index()
    {
        return view("customer.index");
    }

    public function listing(Request $request)
    {
        $url = Route::getFacadeRoot()->current()->uri();

        $categoryDetails = Category::getCatIds($url);

        // echo "<pre>";
        // print_r($categoryDetails) ;
        // die();

        $products = Product::whereIn("category_id", $categoryDetails['catIds']);
        // ->orderBy("product_price", "DESC")->get()->toArray();

        // echo "<pre>";
        // print_r($products) ;
        // die();

        $productColors = Product::select('product_color')->whereIn("category_id", $categoryDetails['catIds'])->distinct()->get();

        $productBrands = Product::with('brand')->select('brand_id')->whereIn("category_id", $categoryDetails['catIds'])->distinct()->get()->toArray();

        if ($request->ajax()) {
            $data = $request->all();
            // echo "<pre>";
            // print_r($data);

            // echo "nxt";

            // DYNAMIK FILTER FOR CHECKED VALUE
            $productFilters = Products_filter::productFilters();
            foreach ($productFilters as $key => $filter) {

                // return ($data[$filter['filter_column']]);

                if (isset($data[$filter['filter_column']]) && !empty($data[$filter['filter_column']])) {
                    $products->whereIn($filter['filter_column'], $data[$filter['filter_column']]);
                }
            }

            // die;

            // FILTER FOR SORT VALUE
            $_GET['sort'] = $data['sort'];
            if (isset($_GET['sort']) && !empty($_GET['sort'])) {

                if ($_GET['sort'] == "letest") {
                    $products = $products->orderby("id", "DESC");
                } elseif ($_GET['sort'] == "lowest_price") {
                    $products = $products->orderby("product_price", "ASC");
                } elseif ($_GET['sort'] == "highest_price") {
                    $products = $products->orderby("product_price", "DESC");
                } elseif ($_GET['sort'] == "a-z") {
                    $products = $products->orderby("product_name", "ASC");
                } elseif ($_GET['sort'] == "z-a") {
                    $products = $products->orderby("product_name", "DESC");
                }

            }

            $products = $products->get();

            return view("customer.listing-product.product", compact('products', 'url', 'productColors','productBrands'));
        } else {
            $products = $products->orderby("products.id", "DESC")->get();
            return view("customer.listing-product.listing", compact('products', 'url', 'categoryDetails', 'productColors','productBrands'));
        };
    }
}