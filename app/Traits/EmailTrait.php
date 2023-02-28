<?php
namespace App\Traits;

use Mail;

trait EmailTrait
{
    public function sendMail($data, $view){
        try {
            Mail::send($view, $data, function ($message) use ($data) {
                $message->subject($data['subject']);
                $message->from('notifications@pmrsloans.com','PMR Loans');
                $message->to($data['email']);
            });
            return true;
        } catch (Exception $e) {
            return false;
        }        
    }

    public function sendContactMail($data, $view){
        try {
            Mail::send($view, $data, function ($message) use ($data) {
                $message->subject($data['subject']);
                $message->from($data['email'], $data['name']);
                $message->to('info@budget-university.com');
            });
            return true;
        } catch (Exception $e) {
        
          return false;
        }       
    }

    public function sendEnquiryMail($data, $view){
        try {
            Mail::send($view, $data, function ($message) use ($data) {
                $message->from($data['fromEmail'], 'Smarty Supply User');
                $message->to($data['email']);
            });
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
     public function sendMailattch($data, $view){
        try {
            $file = public_path('Budget_University_Newsletter.pdf');
            Mail::send($view, $data, function ($message) use ($data,$file) {
                $message->subject($data['subject']);
                $message->from('info@budget-university.com','Newsletter');
                $message->to($data['email']);
                $message->attach($file);
            });
            return true;
        } catch (Exception $e) {
            return false;
        }        
    }
}