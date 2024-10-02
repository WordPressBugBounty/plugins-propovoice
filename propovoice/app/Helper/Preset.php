<?php

namespace Ndpv\Helper;

class Preset
{
    public function default()
    {
        $lead = ['lead' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
            'assign_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                        'level_change_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                        'to_deal_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
        ]
        ];
        $deal = [
                'deal' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                        'assign_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                        'stage_change_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
                ]
        ];
        $estimate = ['estimate' => [
            'default' => [
                'subject' => '{org_name} sent you a Estimate #{id}',
                'msg' => 'Hi <b>{client_name}</b>,
Please find attached Estimate #{id}. Due Date is {due_date}.

Estimate No: #{id}
Estimate Date: {date}
Due Date: {due_date}
Amount: {amount}

Thank you for your business.

Regards
{org_name}'
            ],
            'reminder' => [
                'subject' => '{org_name} sent you a reminder of Estimate #{id}',
                'msg' => 'Hi <b>{client_name}</b>,
Please find attached Estimate #{id}. Due Date was {due_date}.

Estimate No: #{id}
Estimate Date: {date}
Due Date: {due_date}
Amount: {amount}

Thank you for your business.

Regards
{org_name}'
            ],
            'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

            'edit_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

            'accept_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

            'reject_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
        ]];

        $invoice = ['invoice' => [
            'default' => [
                'subject' => '{org_name} sent you a Invoice #{id}',
                'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date is {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
            ],
            'reminder' => [
                'subject' => '{org_name} sent you a reminder of Invoice #{id}',
                'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date was {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
            ],
            'recurring' => [
                'subject' => '{org_name} sent you a recurring Invoice #{id}',
                'msg' => 'Hi <b>{client_name}</b>,
Please find attached Invoice #{id}. Due Date was {due_date}.

Invoice No: #{id}
Invoice Date: {date}
Due Date: {due_date}
Due Amount: {amount}

Thank you for your business.

Regards
{org_name}'
            ],
            'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
            'edit_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
            'paid_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
            'paid_req_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
        ]];

        $client_portal = ['client_portal' => [
            'password' => [
                'subject' => '{org_name} client portal credentials',
                'msg' => 'Hi <b>{client_name}</b>,
{org_name} gives you access to their client portal.
You can log in with the credentials

<b>Login URL:</b> {login_url}
<b>Email:</b> {email}
<b>Password:</b> {password}

Thank you for staying with us.

Regards
{org_name}'
            ]
        ]];

        $team = ['team' => [
            'password' => [
                'subject' => '{org_name} team credentials',
                'msg' => 'Hi <b>{client_name}</b>,
{org_name} gives you access to their team.
You can log in with the credentials

<b>Login URL:</b> {login_url}
<b>Email:</b> {email}
<b>Password:</b> {password}

Thank you for staying with us.

Regards
{org_name}'
            ]
        ]];

        $task = ['task' => [
                'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                'assign_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                'status_change_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
        ]];
        $staff = ['staff' => [
                'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
        ]];
        $project = [
                'project' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],
                        'discussion_add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

                ]
        ];

        $package = [
                'package' => [
                        'assign_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

                ]
        ];
        $order = [
                'order' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

                ]
        ];
        $request = [
                'request' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ],

                ]
        ];

        $file = [
                'file' => [
                        'add_notif' => [
                'subject' => '{org_name} Notification: {notification}',
                'msg' => 'Hi <b>{name}</b>,
You have a notification
{notification_link}

Regards
{org_name}'
            ]
                ]
        ];

        $smtp = ['smtp' => [
            'test' => [
                'subject' => 'Test Mail From {org_name}',
                'msg' => 'Hi,
This is a Test Mail From {org_name}

Regards
{org_name}'
            ]
        ]];

        return [
            'email_template' => array_merge(
                $lead,
                $deal,
                $estimate,
                $invoice,
                $client_portal,
                $team,
                $task,
                $staff,
                $project,
                $package,
                $order,
                $request,
                $file,
                $smtp,
            )
        ];
    }
}
