<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use Illuminate\Http\Request;
use App\Jobs\SendContactUsEmail;

class ContactUsController extends BasePageController
{
    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function contact(Request $request)
    {
        $this->validate($request, [
            'useremail' => 'required',
            'username' => 'required',
        ]);

        if (config('captcha.enabled') === true && (! empty(config('captcha.secret')) && ! empty(config('captcha.sitekey')))) {
            $this->validate($request, [
                'g-recaptcha-response' => 'required|captcha',
            ]);
        }

        $msg = '';

        if ($request->has('useremail')) {
            $email = $request->input('useremail');
            $mailTo = Settings::settingValue('site.main.email');
            $mailBody = 'Values submitted from contact form: ';

            foreach ($request->all() as $key => $value) {
                if ($key !== 'submit' && $key !== '_token' && $key !== 'g-recaptcha-response') {
                    $mailBody .= "$key : $value".PHP_EOL;
                }
            }

            if (! preg_match("/\n/i", $request->input('useremail'))) {
                SendContactUsEmail::dispatch($email, $mailTo, $mailBody);
            }
            $msg = "<h2 style='text-align:center;'>Thank you for getting in touch with ".Settings::settingValue('site.main.title').'.</h2>';
        }
        app('smarty.view')->assign('msg', $msg);

        return redirect('contact-us');
    }

    /**
     * @throws \Exception
     */
    public function showContactForm()
    {
        $theme = Settings::settingValue('site.main.style');
        $title = 'Contact '.Settings::settingValue('site.main.title');
        $meta_title = 'Contact '.Settings::settingValue('site.main.title');
        $meta_keywords = 'contact us,contact,get in touch,email';
        $meta_description = 'Contact us at '.Settings::settingValue('site.main.title').' and submit your feedback';
        $content = app('smarty.view')->fetch($theme.'/contact.tpl');

        app('smarty.view')->assign(
            [
                'title' => $title,
                'content' => $content,
                'meta_title' => $meta_title,
                'meta_keywords' => $meta_keywords,
                'meta_description' => $meta_description,
            ]
        );

        app('smarty.view')->display($theme.'/basepage.tpl');
    }
}
