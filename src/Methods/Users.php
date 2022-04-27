<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 27.04.2022
 * Time: 16:57
 */

namespace FileBrowserClient\Methods;


use Exception;
use FileBrowserClient\Abstracts\Method;
use FileBrowserClient\Methods\Helpers\Rules;
use FileBrowserClient\Token;

class Users extends Method
{
    protected ?array $groups;


    public function setGroups(array $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param string $username
     * @param string $password
     * @param string $group
     * @param false $lockPassword
     * @param null $userPerm
     * @param null $userRules
     * @return array|null
     */
    public function createUser(string $username, string $password, string $group, $lockPassword = false, $userPerm = null, $userRules = null)
    {

        $groups = $this->getGroups();

        if (!$groups) {
            throw new Exception('Set Groups users');
        }

        if (!array_key_exists($group, $groups)) {
            throw new Exception('Groups Not Found');
        }
        $groupData = $groups[$group];

        $perm = !empty($groupData['perm']) ? $groupData['perm'] : [];

        // Общие права и права для группы
        $perm = array_merge([
            "admin" => false,
            "execute" => true,
            "create" => true,
            "rename" => true,
            "modify" => true,
            "delete" => false,
            "share" => true,
            "download" => true
        ], $perm);

        if ($userPerm) {
            // Персональные права для пользователя
            $perm = array_merge($perm, $userPerm);
        }


        $rules = !empty($groupData['rules']) ? $groupData['rules'] : [];
        $rules = array_merge([
            '/.+' => [
                'regex' => true,
                'allow' => false,
            ],
        ], $rules);

        if ($userRules) {
            $rules = array_merge($rules, $userRules);
        }


        $Rules = new Rules();
        foreach ($rules as $path => $data) {
            $path = '/' . ltrim($path, '/');
            if (is_bool($data)) {
                $data = [
                    'regex' => false,
                    'allow' => $data,
                ];
            }
            $Rules->addRule($data['regex'], $data['allow'], $path);
        }


        $data = [
            'what' => "user",
            'which' => [],
            'data' => [
                "id" => 0,
                "username" => $username,
                "password" => $password,
                "scope" => '.', // Директория
                "locale" => 'ru',
                "lockPassword" => $lockPassword,
                "viewMode" => 'list',
                "singleClick" => false, // Открыть в один клик
                "perm" => $perm,
                "commands" => ['ls'],
                "sorting" => [
                    "by" => "name",
                    "asc" => false
                ],
                "rules" => $Rules->getRules(),
                "passsword" => '',
                "dateFormat" => false,
                "hideDotfiles" => false,
            ],
        ];
        return $data;
    }


    public function find(string $username)
    {
        if ($list = $this->list()) {
            foreach ($list as $item) {
                if ($item['username'] == $username) {
                    return $item;
                }
            }
        }
        return null;
    }

    public function findID(int $id)
    {
        if ($list = $this->list()) {
            foreach ($list as $item) {
                if ($item['id'] == $id) {
                    return $item;
                }
            }
        }
        return null;
    }


    /**
     * @param $id
     * @param $data
     * @return bool|string
     */
    public function update($id, $data)
    {
        $data['data']['id'] = $id;
        $data['which'] = ['all'];
        unset($data['data']['passsword']);
        $data['data']['password'] = '';
        return $this->put('/api/users/' . $id, [
            'json' => $data
        ]);
    }


    /**
     * @param $id
     * @return mixed
     */
    public function rm($id)
    {
        return $this->delete('/api/users/' . $id);
    }

    /**
     * @param $data
     * @return bool|string
     */
    public function create($data)
    {
        return $this->post('/api/users', ['json' => $data]);
    }

    public function list()
    {
        $res = $this->get('/api/users');
        if ($res === true) {
            return $this->toArray();
        }
        return null;
    }


}
