<?php

namespace FluentForm\App\Modules\Registerer;

use FluentForm\App\Helpers\Helper;
use FluentForm\App\Http\Controllers\AdminNoticeController;

class ReviewQuery
{
    public function register()
    {
       if( !Helper::isFluentAdminPage()){
           return;
       }
        if (defined('DOING_AJAX') && DOING_AJAX) {
            return;
        }
       add_action('admin_notices',[$this,'show']);
    }
    
    public function show (){
        $notice = new AdminNoticeController();
        $msg = $this->getMessage();
        $notice->addNotice($msg);
        $notice->showNotice();
    }
    
    private function getMessage()
    {
        return [
            'name'    => 'review_query',
            'title'   => '',
            'message' => sprintf('Thank you for using Fluent Forms. We would be very grateful if you could share your experience and leave a review for us in %s',
                '<a target="_blank" href="https://wordpress.org/support/plugin/fluentform/reviews/#new-post">Wordpress.org</a>. Your reviews inspires us to keep improving the plugin and delivering a better user experience.'),
            'links'   => [
                [
                    'href'     => 'https://wordpress.org/support/plugin/fluentform/reviews/#new-post',
                    'btn_text' => 'Yes',
                    'btn_atts' => 'class="el-button--primary el-button--mini ff_review_now" data-notice_name="review_query"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Maybe Later',
                    'btn_atts' => 'class="el-button el-button--secondary el-button--mini ff_nag_cross" data-notice_type="temp" data-notice_name="review_query"',
                ],
                [
                    'href'     => admin_url('admin.php?page=fluent_forms'),
                    'btn_text' => 'Do not show again',
                    'btn_atts' => 'class="el-button--secondary el-button--mini ff_nag_cross" data-notice_type="permanent" data-notice_name="review_query"',
                ],
            ],
        ];
    }
}