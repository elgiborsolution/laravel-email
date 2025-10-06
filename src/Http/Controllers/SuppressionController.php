<?php

namespace ESolution\LaravelEmail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ESolution\LaravelEmail\Models\Suppression;

class SuppressionController extends Controller
{
    public function index(Request $r)
    {
        return Suppression::query()
            ->when($r->query('q'), fn($q,$v)=>$q->where('email','like',"%$v%"))
            ->orderByDesc('id')->paginate(50);
    }

    public function store(Request $r)
    {
        $data = $r->validate([
            'email' => 'required|email',
            'reason' => 'nullable|in:unsubscribe,bounce,spam,manual',
        ]);
        $data['email'] = strtolower($data['email']);
        $data['reason'] = $data['reason'] ?? 'manual';
        return Suppression::updateOrCreate(['email'=>$data['email']], ['reason'=>$data['reason']]);
    }

    public function destroy(Suppression $suppression)
    {
        $suppression->delete();
        return response()->json(['deleted'=>true]);
    }
}
