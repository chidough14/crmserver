<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    public function createCompany (Request $request) {
        $request->validate([
            'name'=> 'required',
            'email'=> 'required|email',
            'phone'=> 'required',
            'address'=> 'required',
        ]);

        $company = Company::create([
            'name'=> $request->name,
            'email'=> $request->email,
            'phone'=> $request->phone,
            'address'=> $request->address,
            'contactPerson'=> $request->contactPerson
        ]);

        return response([
            'company'=> $company,
            'message' => 'Company created successfully',
            'status' => 'success'
        ], 201);
    }


    public function getCompanies () {
        $companies = Company::paginate(5);

        return response([
            'companies'=> $companies,
            'message' => 'Companies results',
            'status' => 'success'
        ], 201);
    }

    public function search (Request $request) {
        $text = $request->query('query');
        $companies = Company::where('name', 'like', '%'.$text.'%')->get();

        return response([
            'companies'=> $companies,
            'message' => 'Companies results',
            'status' => 'success'
        ], 201);
    }

    public function getSingleCompany ($companyId){
        $company = Company::where('id', $companyId)->with('activities')->first();
        

        return response([
            'company'=> $company,
            'message' => 'Company results',
            'status' => 'success'
        ], 201);
    }

    public function updateCompany (Request $request, $companyId){
        $company = Company::where('id', $companyId)->first();

        $company->update($request->all());

        return response([
            'company'=> $company,
            'message' => 'Company results',
            'status' => 'success'
        ], 201);
    }

    public function deleteCompany ($companyId){
        $company = Company::where('id', $companyId)->first();
        
        $company->delete();

        return response([
            'message' => 'Company deleted',
            'status' => 'success'
        ], 201);
    }

    public function addCompanyToList (Request $request, $companyId){
        $company = Company::where('id', $companyId)->first();
        $listId = $request->listId;
        
        $company->lists()->syncWithoutDetaching($listId);

        return response([
            'message' => 'Company Added',
            'status' => 'success'
        ], 201);
    }

    public function deleteCompanyFromList (Request $request, $companyId){
        $company = Company::where('id', $companyId)->first();
        $listId = $request->listId;
        
        $company->lists()->detach($listId);

        return response([
            'message' => 'Company deleted',
            'status' => 'success'
        ], 201);
    }

    public function bulkDeleteCompanies (Request $request){
        Company::whereIn('id', $request->companyIds)->delete();

        return response([
            'message' => 'Companies deleted',
            'status' => 'success'
        ], 201);
    }

    public function bulkAddCompanies (Request $request){
        $newRecords = collect($request->companiesPayload)->map(function ($item) {
            return Company::create($item);
        });

        return response([
            'companies'=> $newRecords,
            'message' => 'Companies added',
            'status' => 'success'
        ], 201);
    }
}
