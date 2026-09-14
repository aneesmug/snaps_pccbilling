<?php

/*
|--------------------------------------------------------------------------
| Controller
|--------------------------------------------------------------------------
|
*/

if (!defined('APP_RUN')) {
    exit('No direct access allowed');
}

_auth();
$ui->assign('selected_navigation', 'util');
$ui->assign('_title', __('AI') . '- ' . $config['CompanyName']);
$action = route(1, 'responses');
$user = authenticate_admin();

if (!has_access($user->roleid, 'hr', 'view')) {
    permissionDenied();
}

$app_environment = appEnvironment();

$request_method = strtolower($_SERVER['REQUEST_METHOD']);

switch ($action) {

    case 'agents':

        if($request_method == 'get')
        {
            $ui->assign('selected_navigation', 'agents');

            $agent = route(2);

            if($agent == 'marketing-strategy-builder')
            {
                $marketing_plans = AiResponse::query()
                    ->where('related_to', 'ai_agent_generate_marketing_plan')
                    ->orderBy('created_at', 'desc')
                    ->get();

                $selected_plan_id = route(3);
                $selected_plan = null;
                if($selected_plan_id)
                {
                    $selected_plan = AiResponse::where('uuid', $selected_plan_id)->first();
                }

                if(!$selected_plan)
                {
                    $selected_plan = AiResponse::query()->orderBy('created_at', 'desc')->first();
                }

                $plan_details = null;

                if(!empty($selected_plan->reply))
                {
                    //Check if the reply is a JSON
                    $json = json_decode($selected_plan->reply);

                    if(json_last_error() === JSON_ERROR_NONE)
                    {
                        $plan_details = $json;
                    }
                }

               // ray('plan_details', $plan_details);

                view('agents/marketing-strategy-builder/views/ui', [
                    'agent' => 'marketing-strategy-builder',
                    'marketing_plans' => $marketing_plans,
                    'selected_plan' => $selected_plan,
                    'plan_details' => $plan_details,
                ]);
            }
        }

        if($request_method == 'post')
        {
            $sub_action = route(2);

            if($sub_action == 'generate-strategy')
            {

                if($app_environment == 'demo')
                {
                    appFlashMessage('This feature is not available in Demo Mode.', 'error');
                    exit();
                }

                $data = getJsonPostData();

                if(!empty($data['business_name']) && !empty($data['business_short_description']))
                {
                    $business_name = $data['business_name'];
                    $business_short_description = $data['business_short_description'];

                    $prompt = appPromptBuilder('agent-generate-marketing-plan',[
                        'business_name' => $business_name,
                        'business_short_description' => $business_short_description,
                    ]);

                    $ai_response = new AiResponse();
                    $ai_response->uuid = Str::uuid();
                    $ai_response->related_to = 'ai_agent_generate_marketing_plan';
                    $ai_response->title = $business_name.'- '.__('Marketing Plan');
                    $ai_response->prompt = $prompt;
                    $ai_response->save();



                    $client = appOpenAIClient($config);

                    if(!$client)
                    {
                        appFlashMessage('API Key is not configured.', 'error');
                        exit();
                    }

                    $result = $client->chat()->create([
                        'model' => 'gpt-4o',
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                        'response_format' => [
                            'type' => 'json_object',
                        ],
                    ]);

                    if(!empty($result['error']['message']))
                    {
                        $ai_response->error = $result['error']['message'];
                        $ai_response->save();
                    }
                    else
                    {
                        $ai_response->reply = $result['choices'][0]['message']['content'];
                        $ai_response->is_json = true;
                        $ai_response->save();
                    }

                }

            }

        }


        break;

    case 'responses':
        $responses = AiResponse::all();

        view('ai-responses', [
            'responses' => $responses,
        ]);

        break;

        case 'response':

            $uuid = route(2);

            $response = AiResponse::where('uuid', $uuid)->first();

            if(!$response)
            {
                abort(404);
            }

            view('ai-response', [
                'response' => $response,
            ]);

            break;

            case 'delete':
                $id = route(3);
                $response = AiResponse::find($id);
                if($response)
                {
                    $response->delete();
                    r2(U . 'ai/responses', 's', $_L['delete_successful']);
                }
                break;

    default:
        abort(404);
        break;

}