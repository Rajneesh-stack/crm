<?php

/*
 | Free-text message templates used in the lead Communication panel.
 |
 | Placeholders: {name}, {course}, {counselor}, {phone}, {email}
 |
 | NOTE: WhatsApp Cloud API only allows free-text within a 24-hour window
 | after the customer last messaged you. For first-touch outreach you MUST
 | use an APPROVED template message (configured in Meta Business Manager).
 | Approved template names go under `whatsapp_approved_templates` below.
 */
return [

    'text_templates' => [
        'greeting' => [
            'label' => 'Greeting',
            'body'  => "Hello {name}, thanks for showing interest in our {course} program. I'll be your point of contact going forward. Feel free to reply here with any questions!",
        ],
        'callback' => [
            'label' => 'Missed call follow-up',
            'body'  => "Hi {name}, I just tried calling you about the {course} program but couldn't reach you. When would be a good time for a quick chat?",
        ],
        'fee_info' => [
            'label' => 'Fee information',
            'body'  => "Hi {name}, here are the details for the {course} program. I'll share the full brochure shortly. Let me know what works best for you.",
        ],
        'reminder' => [
            'label' => 'Reminder',
            'body'  => "Hi {name}, just a quick reminder about our discussion on the {course} program. Let me know if you'd like to proceed or have any questions.",
        ],
        'thanks' => [
            'label' => 'Thank you',
            'body'  => "Thank you {name}! It was great talking to you. I'll follow up shortly with the next steps.",
        ],
    ],

    'whatsapp_approved_templates' => [
        // 'hello_world' => [
        //     'label'   => 'Meta hello_world',
        //     'name'    => 'hello_world',
        //     'language'=> 'en_US',
        //     'params'  => [], // names of body placeholders, in order
        // ],
    ],

    'email_templates' => [
        'introduction' => [
            'label'   => 'Introduction',
            'subject' => 'Following up on your interest in {course}',
            'body'    => "Hi {name},\n\nThanks for showing interest in our {course} program. I'm {counselor} and I'll be guiding you through the next steps.\n\nWhen would be a convenient time for a 15-minute call?\n\nWarm regards,\n{counselor}",
        ],
        'brochure' => [
            'label'   => 'Brochure share',
            'subject' => 'Details: {course} program',
            'body'    => "Hi {name},\n\nAs discussed, please find the details for the {course} program. Do reply if you have any questions.\n\nRegards,\n{counselor}",
        ],
    ],

];
