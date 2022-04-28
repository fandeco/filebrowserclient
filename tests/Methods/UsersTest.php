<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 27.04.2022
 * Time: 17:15
 */

namespace FileBrowserClient\Tests\Methods;

use FileBrowserClient\Methods\Users;
use FileBrowserClient\Tests\TestCase;

class UsersTest extends TestCase
{

    public function testCreateUser()
    {
        $groups = [
            'root' => [
                'rules' => [
                    '/.+' => [
                        'regex' => true,
                        'allow' => true,
                    ]
                ],
                "perm" => [
                    "admin" => false,
                    "execute" => true,
                    "create" => true,
                    "rename" => true,
                    "modify" => true,
                    "delete" => true,
                    "share" => true,
                    "download" => true
                ],
            ],
            'admin' => [
                'rules' => [
                    '/.+' => true
                ],
                "perm" => [
                    "admin" => false,
                    "execute" => false,
                    "create" => true,
                    "rename" => true,
                    "modify" => true,
                    "delete" => true,
                    "share" => true,
                    "download" => true
                ],
            ],
            'content_manager' => [
                'rules' => [
                    '/.+' => true
                ],
                "perm" => [
                    "admin" => false,
                    "execute" => false,
                    "create" => false,
                    "rename" => false,
                    "modify" => false,
                    "delete" => false,
                    "share" => true,
                    "download" => true
                ],
            ],
            'employee' => [
                'perm' => [
                    "admin" => false,
                    "execute" => false,
                    "create" => true,
                    "rename" => false,
                    "modify" => false,
                    "delete" => false,
                    "share" => true,
                    "download" => true
                ],
                'rules' => [
                    '/.+' => [
                        'regex' => true,
                        'allow' => false,
                    ],
                    'Каталоги' => true,
                    'Контент Instagram' => true,
                    'ПРАЙС-ЛИСТЫ' => true,
                    'Рекламные материалы' => true,
                    'WEB семинары' => true,
                    'Презентации' => true,
                ]
            ]
        ];
        $User = new Users();

        $list = $User->list();
        echo '<pre>';
        print_r($list);
        die;

        $User->setGroups($groups);
        $users = [
            [
                'username' => 'stepanenko',
                'password' => '123123',
                'group' => 'root',
            ],
            [
                'username' => 'stepanenko2',
                'password' => '123123',
                'group' => 'root',
            ]
        ];

        foreach ($users as $k => $data) {
            $username = $data['username'];
            $password = $data['password'];
            $group = $data['group'];
            try {
                $data = $User->createUser($username, $password, $group);
                if ($oldUser = $User->find($username)) {
                    $res = $User->update($oldUser['id'], $data);
                } else {
                    $res = $User->create($data);
                }

                $users[$k]['success'] = $res;
                $users[$k]['data'] = $data;
            } catch (\Exception $e) {
                print_r([
                    'msg' => $e->getMessage(),
                    'user' => $data,
                ]);
                die;
            }
        }
    }

}
