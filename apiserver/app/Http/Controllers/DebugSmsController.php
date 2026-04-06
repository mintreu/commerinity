<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Sms\SmsTemplate;
use App\Services\IntegrationServices\Sms\SmsService;
use Illuminate\Http\JsonResponse;

final class DebugSmsController extends Controller
{
    public function testDltSingle(SmsService $smsService): JsonResponse
    {




        if (! app()->environment(['local', 'testing'])) {
            abort(403, 'Debug SMS route is only available in local/testing.');
        }

        // Optional query overrides:
        // /__testing/sms/dlt-single?template_slug=otp-general&mobile=9800777600
        $templateSlug = (string) request()->string('template_slug', 'otp-general');
        $targetMobile = (string) request()->string('mobile', '9800777600');

        $template = SmsTemplate::query()
            ->where('slug', $templateSlug)
            ->firstOrFail();

        $variables = $this->sampleVariables($template);

        $response = $smsService->sendTemplateSingle(
            phone: $targetMobile,
            templateSlug: (string) $template->slug,
            variables: $variables,
            type: 'transactional',
            userId: null,
        );

        return response()->json([
            'success' => $response->success,
            'status' => $response->status,
            'message' => $response->message,
            'request_id' => $response->requestId,
            'error_code' => $response->errorCode,
            'error_message' => $response->errorMessage,
            'template' => [
                'id' => $template->id,
                'slug' => $template->slug,
                'message_id' => $template->message_id,
                'sender_id' => $template->sender_id,
                'variables' => $template->variables,
            ],
            'target_mobile' => $targetMobile,
            'variables_values' => $template->getVariablesPipeString($variables),
            'provider_data' => $response->providerData,
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function sampleVariables(SmsTemplate $template): array
    {
        $map = [
            'number' => '123456',
            'purpose' => 'login',
            'amount' => '250.00',
            'name' => 'Rahul Das',
            'application_id' => 'APP-2604-AB12CD34',
            'action' => 'credited',
            'balance' => '500.00',
            'status' => 'activated',
            'plan' => 'Starter',
            'reference' => 'SUB-8F3A21D9',
            'app_name' => (string) config('app.name'),
        ];

        $variables = [];
        foreach ((array) $template->variables as $key) {
            $variables[(string) $key] = $map[(string) $key] ?? 'sample';
        }

        return $variables;
    }
}
