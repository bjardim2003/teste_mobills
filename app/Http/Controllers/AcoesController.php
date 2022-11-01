<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Quote;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AcoesController extends Controller
{

    public function index () {

        //$token = "sk_ac16f96d969f44c29bcef70c2868396d";

        //$symbol = ["aapl","fb","twtr"];
        //foreach ($symbol as $sb) {
        //    $response[$sb] = $this->getResponseIEX($token, $sb);
        //}
        $response = [];
        return view('welcome', compact('response'));

    }

    public function consulta (Request $request) {

        $token = "sk_ac16f96d969f44c29bcef70c2868396d";

        $validated = $request->validate([
            'symbol' => 'required',
        ]);

        $response = $this->getResponseIEX($token, $request->symbol);

        return view('welcome', compact('response'));

    }

    /**
     * @param string $token
     * @param string $symbol
     * @return array
     */
    public function getResponseIEX(string $token, string $symbol): array
    {
        $company = $this->setCompany($symbol, $token);
        $response['data'] = $company;

        $quoteDB = $this->setQuote($symbol, $token, $company);
        $response['quote'] = $quoteDB;

        return $response;
    }

    /**
     * @param string $symbol
     * @param string $token
     * @return Company
     */
    public function setCompany(string $symbol, string $token): Company
    {
        $company = Company::where('symbol', $symbol)->first();
        if (!$company) {

            $responseCompany = Http::withHeaders([
                'Accept' => 'text/event-stream',
            ])->get('https://cloud.iexapis.com/stable/stock/' . $symbol . '/company?token=' . $token);

            $companyObject = $responseCompany->object();

            $company = new Company();
            $company->name = $companyObject->companyName;
            $company->symbol = $companyObject->symbol;
            $company->exchange = $companyObject->exchange;
            $company->industry = $companyObject->industry;
            $company->website = $companyObject->website;
            $company->description = $companyObject->description;
            $company->sector = $companyObject->sector;
            $company->securityName = $companyObject->securityName;
            $company->primarySicCode = $companyObject->primarySicCode;
            $company->employees = $companyObject->employees;
            $company->address = $companyObject->address;
            $company->address2 = $companyObject->address2;
            $company->state = $companyObject->state;
            $company->city = $companyObject->city;
            $company->zip = $companyObject->zip;
            $company->country = $companyObject->country;
            $company->phone = $companyObject->phone;
            $company->save();

        }
        return $company;
    }

    /**
     * @param string $symbol
     * @param string $token
     * @param Company $company
     * @return Quote
     */
    public function setQuote(string $symbol, string $token, Company $company): Quote
    {
        $quote = Http::withHeaders([
            'Accept' => 'text/event-stream',
        ])->get('https://cloud.iexapis.com/stable/stock/' . $symbol . '/quote?token=' . $token);
        $quoteOb = $quote->object();
        $quoteDB = Quote::where('symbol', $symbol)->where('latestUpdate', '>=', $quoteOb->latestUpdate)->first();
        if (!$quoteDB) {
            $quoteDB = new Quote();
            $quoteDB->company_id = $company->id;
            $quoteDB->latestPrice = $quoteOb->latestPrice;
            $quoteDB->latestUpdate = $quoteOb->latestUpdate;
            $quoteDB->symbol = $quoteOb->symbol;
            $quoteDB->save();
        }
        return $quoteDB;
    }

}
