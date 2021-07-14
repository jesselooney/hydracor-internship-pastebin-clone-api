<?php
declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\Auth\DefaultPasswordHasher;

class UsersController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow(['add', 'login']);
    }

    public function view()
    {
        $user = $this->getUser();
        $this->set('user', $user);
        $this->viewBuilder()->setOption('serialize', ['user']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $user = $this->Users->newEntity($this->request->getData());
        if ($this->Users->save($user)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'user' => $user,
        ]);
        $this->viewBuilder()->setOption('serialize', ['user', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function edit()
    {
        $this->request->allowMethod(['patch', 'post', 'put']);
        $user = $this->getUser();
        $user = $this->Users->patchEntity($user, $this->request->getData());
        if ($this->Users->save($user)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'user' => $user,
        ]);
        $this->viewBuilder()->setOption('serialize', ['user', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function delete()
    {
        $this->request->allowMethod(['delete']);
        $user = $this->getUser();
        $message = 'Deleted';
        if (!$this->Users->delete($user)) {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function login()
    {
        $this->request->allowMethod(['post']);
        $credentials = $this->request->input('json_decode');
        $user = $this->Users->findByEmail($credentials->email)->first();
        if ($user && (new DefaultPasswordHasher())->check(
                $credentials->password,
                $user->password
        )) {
            $payload = [
                "sub" => $user->id,
                "exp" => time() + 600, // Expire in 10 minutes
            ];
            $jwt = JWT::encode($payload, Security::getSalt());
            $message = 'Success';
        } else {
            $jwt = '';
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'token' => $jwt,
        ]);
        $this->viewBuilder()->setOption('serialize', ['token', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    protected function getUser()
    {
        $jwt = $this->request->getQuery('token');
        $decoded = JWT::decode($jwt, Security::getSalt(), array('HS256'));
        return $this->Users->get($decoded->sub);
    }
}