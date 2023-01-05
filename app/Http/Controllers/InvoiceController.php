<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Invoice;
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
        $invoice->products;

        return response([
            'invoice'=> $invoice,
            'message' => 'Invoice created successfully',
            'status' => 'success'
        ], 201);
    }

    public function getInvoices () {
        $invoices = Invoice::all();

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
}


// {
//     "payment_method": "Cash",
//     "invoice_no": "AA333999",
//     "billing_address": "25 Sterling road",
//     "reference": "Paul White",
//     "user_id": 2,
//      "activity_id": 16
// }
