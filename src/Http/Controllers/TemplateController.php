<?php

namespace ESolution\LaravelEmail\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use ESolution\LaravelEmail\Models\EmailTemplate;

class TemplateController extends Controller
{
    public function store(Request $r)
    {
        $data = $r->validate([
            'name' => 'required|string|unique:le_email_templates,name',
            'subject' => 'required|string',
            'html' => 'nullable|string',
            'text' => 'nullable|string',
            'from_email' => 'nullable|email',
            'from_name' => 'nullable|string',
        ]);
        return EmailTemplate::create($data);
    }

    public function index()
    {
        return EmailTemplate::query()->latest()->paginate(50);
    }
}
