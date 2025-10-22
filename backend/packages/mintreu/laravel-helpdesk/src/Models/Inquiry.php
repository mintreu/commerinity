<?php

namespace Mintreu\LaravelHelpdesk\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
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
