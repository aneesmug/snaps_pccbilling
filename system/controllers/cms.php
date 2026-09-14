<?php
if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

authenticate_admin();

$ui->assign('selected_navigation', 'cms');

$action = route(1);
if ($request_method === 'GET') {
    switch ($action) {
        case '':
        case 'posts':

            $posts = Post::all();
            $home_page = Post::where('is_home_page', 1)->first();

            $terms_and_conditions_page = Post::query()->where('api_name', 'terms_and_conditions')->first();
            $privacy_policy_page = Post::query()->where('api_name', 'privacy_policy')->first();

            \view('posts', [
                'posts' => $posts,
                'home_page' => $home_page,
                'terms_and_conditions_page' => $terms_and_conditions_page,
                'privacy_policy_page' => $privacy_policy_page,
            ]);

            break;

        case 'post':
            $id = route(2);
            $post = Post::find($id);
            if($post)
            {
                $post_settings = $post->settings;
                \view('post', [
                    'post' => $post,
                    'post_settings' => $post_settings,
                ]);
            }

            break;


            case 'create-post':

                $type = route(2);

                switch ($type)
                {
                    case 'home':

                        $post = Post::where('is_home_page', 1)->first();

                        if($post)
                        {
                            $post->is_home_page = 0;
                            $post->save();
                        }

                        $post = new Post();
                        $post->title = 'Home Page';
                        $post->name = 'Home Page';
                        $post->slug = 'home';
                        $post->is_home_page = 1;
                        $post->type = 'page';
                        $post->api_name = 'page';
                        $post->save();

                        redirect_to('cms/post/' . $post->id);

                        break;

                        case 'terms_and_conditions':

                            $post = Post::where('api_name', 'terms_and_conditions')->first();
                            if(!$post){
                                $post = new Post();
                                $post->title = 'Terms and Conditions';
                                $post->name = 'Terms and Conditions';
                                $post->slug = 'terms-and-conditions';
                                $post->type = 'page';
                                $post->api_name = 'terms_and_conditions';
                                $post->save();
                            }

                            redirect_to('cms/post/' . $post->id);

                            break;

                        case 'privacy_policy':

                            $post = Post::where('api_name', 'privacy_policy')->first();
                            if(!$post){
                                $post = new Post();
                                $post->title = 'Privacy Policy';
                                $post->name = 'Privacy Policy';
                                $post->slug = 'privacy-policy';
                                $post->type = 'page';
                                $post->api_name = 'privacy_policy';
                                $post->save();
                            }

                            redirect_to('cms/post/' . $post->id);

                            break;

                }

                break;

        case 'configure':

            \view('configure', [

            ]);

            break;
    }
}

if ($request_method === 'POST') {

    $data = request()->all();

    switch ($action) {
        case 'post':

            ray($data);

            $id = $data['id'];
            $post = Post::find($id);
            if($post)
            {
                $post->title = $data['title'] ?? null;
                $post->settings = $data['settings'] ?? null;
                $post->content = $data['content'] ?? null;
                $post->save();
            }

            api_response([
                'status' => 'success',
                'message' => __('Saved successfully'),
            ]);

            break;

        case 'save-settings':

            updateOption('announcement_bar_message', $data['announcement_bar_message'] ?? null, true);
            updateOption('announcement_bar_background_color', $data['announcement_bar_background_color'] ?? null, true);
            updateOption('announcement_bar_link_text', $data['announcement_bar_link_text'] ?? null, true);
            updateOption('announcement_bar_link_url', $data['announcement_bar_link_url'] ?? null, true);
            updateOption('contact_widget_title', $data['contact_widget_title'] ?? null, true);
            updateOption('contact_widget_description', $data['contact_widget_description'] ?? null, true);
            updateOption('quick_links_widget_1_title', $data['quick_links_widget_1_title'] ?? null, true);
            updateOption('quick_links_widget_2_title', $data['quick_links_widget_2_title'] ?? null, true);

            updateOption('social_twitter', $data['social_twitter'] ?? null, true);
            updateOption('social_facebook', $data['social_facebook'] ?? null, true);
            updateOption('social_instagram', $data['social_instagram'] ?? null, true);
            updateOption('social_youtube', $data['social_youtube'] ?? null, true);
            updateOption('social_linkedin', $data['social_linkedin'] ?? null, true);
            updateOption('social_pinterest', $data['social_pinterest'] ?? null, true);

            $quick_links_widget_1_links_items = $data['quick_links_widget_1_links'] ?? null;
            $quick_links_widget_1_links = null;

            if(!empty($quick_links_widget_1_links_items['text']))
            {
                $i = 0;
                foreach ($quick_links_widget_1_links_items['text'] as $text)
                {

                    if(empty($text))
                    {
                        continue;
                    }

                    $quick_links_widget_1_links[] = [
                        'text' => $text,
                        'url' => $quick_links_widget_1_links_items['url'][$i],
                    ];

                    $i++;
                }
            }

            if(!empty($quick_links_widget_1_links))
            {
                $quick_links_widget_1_links = json_encode($quick_links_widget_1_links);
            }

            updateOption('quick_links_widget_1_links', $quick_links_widget_1_links, true);

            $quick_links_widget_2_links_items = $data['quick_links_widget_2_links'] ?? null;
            $quick_links_widget_2_links = null;

            if(!empty($quick_links_widget_2_links_items['text']))
            {
                $i = 0;
                foreach ($quick_links_widget_2_links_items['text'] as $text)
                {

                    if(empty($text))
                    {
                        continue;
                    }

                    $quick_links_widget_2_links[] = [
                        'text' => $text,
                        'url' => $quick_links_widget_2_links_items['url'][$i],
                    ];

                    $i++;
                }
            }

            if(!empty($quick_links_widget_2_links))
            {
                $quick_links_widget_2_links = json_encode($quick_links_widget_2_links);
            }

            updateOption('quick_links_widget_2_links', $quick_links_widget_2_links, true);

            redirect_to('cms/configure');

            break;


    }

}
