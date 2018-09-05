<?php

namespace App\Http\Controllers;

use App\User;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Validator;
use Vyuldashev\XmlToArray\XmlToArray;

class AuthController extends Controller {

    private $user;

    /**
     * AuthController constructor.
     */
    public function __construct(User $user) {
        $this->user = $user;
    }

    public function test() {
        $client = new Client();
        $response = $client->get('https://jsonplaceholder.typicode.com/todos/1');
        try {
            $response2 = $client->request('GET', 'http://testxml.e-tsw.com/AffiliateService/AffiliateService.svc/restful/GetQuoteHotels?a=HOSTECHXML&co=MX&c=pe&sd=20180801&ed=20180804&h=&rt=&mp=&r=1&r1a=1&d=2&l=esp');
        } catch (GuzzleException $e) {
            print_r($e->getTraceAsString());
        }
        $result = XmlToArray::convert($response2->getBody()->getContents());
        return response()->json($result);
    }

    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {

        $validator = Validator::make($request->all(), [
            'name' => 'required|min:6',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
            'c_password' => 'required|min:6|same:password',
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 401);
        }

        $this->user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $this->user->createToken('TutsForWeb')->accessToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request) {

        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:6'
        ]);

        if ($validator->fails()) {
            return response()->json(['error'=>$validator->errors()], 500);
        }

        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($credentials)) {
            $token = auth()->user()->createToken('TutsForWeb')->accessToken;
            return response()->json(['token' => $token], 200);
        } else {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }
}
