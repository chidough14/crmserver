<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Invoice;
use App\Models\StripePayment;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function createInvoice (Request $request) {
        $request->validate([
            'invoice_no'=> 'required',
            'payment_method'=> 'required',
            'billing_address'=> 'required',
            'reference'=> 'required'
        ]);

        $invoice = Invoice::create($request->all());

        $activity = Activity::where("id", $invoice->activity_id)->first();

        $arrIds = array();
        $arrQuantity = array();
        foreach ($activity->products as $key => $value) {
           $arrIds[] =  $value->id;
           $arrQuantity[] = $value->pivot->quantity;
        }

        $sync_data = [];
        for($i = 0; $i < count($arrIds); $i++)
            $sync_data[$arrIds[$i]] = ['quantity' => $arrQuantity[$i]];

        $invoice->products()->attach($sync_data);
        //$invoice->products;

        return response([
            'invoice'=> $invoice,
            'message' => 'Invoice created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getInvoices () {
        $invoices = Invoice::with("activity")->where("user_id", auth()->user()->id)->paginate(5);

        return response([
            'invoices'=> $invoices,
            'message' => 'All invoices',
            'status' => 'success'
        ], 201);
    }

    public function filterInvoices ($critera) {
        if ($critera === "1month") {
            $invoices = Invoice::with("activity")
            ->where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(30)->endOfDay())
            ->paginate(5);
        } 

        if ($critera === "3months") {
            $invoices = Invoice::with("activity")
            ->where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(60)->endOfDay())
            ->paginate(5);
        } 

        if ($critera === "12months") {
            $invoices = Invoice::with("activity")
            ->where("user_id", auth()->user()->id)
            ->where('created_at', '>', now()->subDays(365)->endOfDay())
            ->paginate(5);
        } 
       

        return response([
            'invoices'=> $invoices,
            'message' => 'All invoices',
            'status' => 'success'
        ], 201);
    }

    public function getSingleInvoice ($invoiceId) {
        $invoice = Invoice::where("id", $invoiceId)->first();

        $invoice->products;

        return response([
            'invoice'=> $invoice,
            'message' => 'Invoice',
            'status' => 'success'
        ], 201);
    }

    public function updateInvoice (Request $request, $invoiceId) {
        $invoice = Invoice::where("id", $invoiceId)->first();

        $invoice->update($request->all());
        $invoice->products;
        $invoice->activity;
        

        return response([
            'invoice'=> $invoice,
            'message' => 'Invoice updated',
            'status' => 'success'
        ], 201);
    }

    public function deleteInvoice ($invoiceId) {
        $invoice = Invoice::where("id", $invoiceId)->first();

        $invoice->delete();

        return response([
            'message' => 'Invoice deleted',
            'status' => 'success'
        ], 201);
    }

    public function addUpdateProduct (Request $request, $invoiceId) {

        $invoice = Invoice::where("id", $invoiceId)->first();
        $productId = $request->productId;
        $quantity = $request->quantity;

        $invoice->products()->sync([$productId => [ 'quantity' => $quantity] ], false);

        return response([
            'product'=> $invoice->products()->where('product_id', $productId)->first(),
            'message' => 'Product added',
            'status' => 'success'
        ], 201);
    }

    public function deleteProduct (Request $request, $invoiceId) {

        $invoice = Invoice::where("id", $invoiceId)->first();
        $invoice->products()->detach($request->productId);
      
        return response([
            'message' => 'Product deleted',
            'status' => 'success'
        ], 201);
    }

    public function saveStripeOrder (Request $request) {
       $order = new StripePayment();

       $order->user_id = $request->user_id;
       $order->products = json_encode($request->products);
       $order->activity_id = $request->activity_id;
       $order->total = $request->total;
       $order->subtotal = $request->subtotal;
       $order->shipping = json_encode($request->shipping);
       $order->payment_status = $request->payment_status;
       $order->delivery_status = $request->delivery_status;

       $order->save();

        return response([
            'order'=> $order,
            'message' => 'Order created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getStripeOrders() {
        $orders = StripePayment::where("user_id", auth()->user()->id)->paginate(5);

        foreach ($orders as $order) {
            $order['products'] = json_decode($order['products'], true);
            $order['shipping'] = json_decode($order['shipping'], true);
        }

        return response([
            'orders'=> $orders,
            'message' => 'All stripe orders',
            'status' => 'success'
        ], 201);
    }
}




// {
//     "payment_method": "Cash",
//     "invoice_no": "AA333999",
//     "billing_address": "25 Sterling road",
//     "reference": "Paul White",
//     "user_id": 2,
//      "activity_id": 16
// }
// paymentTerm
// total, email
