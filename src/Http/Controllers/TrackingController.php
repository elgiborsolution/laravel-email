<?php

namespace ESolution\LaravelEmail\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackingController extends Controller
{
    public function pixel($token)
    {
        $id = DB::table('le_broadcast_recipients')->where('token', $token)->value('id');
        if ($id) {
            DB::table('le_broadcast_recipients')->where('id', $id)->whereNull('opened_at')->update(['opened_at' => now()]);
        }
        $gif = base64_decode('R0lGODlhAQABAPAAAAAAAAAAACH5BAEAAAAALAAAAAABAAEAAAICRAEAOw==');
        return new Response($gif, 200, ['Content-Type' => 'image/gif', 'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0']);
    }

    public function unsubscribe($token)
    {
        $row = DB::table('le_broadcast_recipients')->select('id','email')->where('token',$token)->first();
        if ($row) {
            DB::table('le_broadcast_recipients')->where('id',$row->id)->update(['unsubscribed_at' => now()]);
            DB::table('le_suppressions')->updateOrInsert(['email'=>strtolower($row->email)], ['reason'=>'unsubscribe','updated_at'=>now(),'created_at'=>now()]);
        }
        return response('You have been unsubscribed.', 200)->header('Content-Type', 'text/plain');
    }
}
