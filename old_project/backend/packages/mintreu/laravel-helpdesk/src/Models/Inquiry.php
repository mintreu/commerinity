<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    /** @use HasFactory<\Database\Factories\InquiryFactory> */
    use HasFactory;


    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
        'company_name',
        'address',
        'website',
        'is_business', // boolean flag: true = business enquiry, false = general contact
    ];






}
