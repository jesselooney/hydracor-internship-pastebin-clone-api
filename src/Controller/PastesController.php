<?php
declare(strict_types=1);

namespace App\Controller;

use Firebase\JWT\JWT;
use Cake\Utility\Security;

class PastesController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->loadComponent('RequestHandler');
        $this->Auth->allow([]);
    }

    public function index()
    {
        $pastes = $this->Pastes->find('all')->where(
            ['Pastes.user_id' => $this->getUserId()]
        );
        $this->set('pastes', $pastes);
        $this->viewBuilder()->setOption('serialize', ['pastes']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function view($id)
    {
        $paste = $this->Pastes->get($id);

        if ($paste->user_id != $this->getUserId()) {
            $this->set([
                'message' => 'Unauthorized',
                'paste' => ''
            ]);
        } else {
            $this->set([
                'message' => 'Success',
                'paste' => $paste
            ]);
        }
        $this->viewBuilder()->setOption('serialize', ['paste', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function add()
    {
        $this->request->allowMethod(['post', 'put']);
        $paste = $this->Pastes->newEntity($this->request->getData());
        $paste->user_id = $this->getUserId();
        if ($this->Pastes->save($paste)) {
            $message = 'Saved';
        } else {
            $message = 'Error';
        }
        $this->set([
            'message' => $message,
            'paste' => $paste,
        ]);
        $this->viewBuilder()->setOption('serialize', ['paste', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function edit($id)
    {
        $this->request->allowMethod(['patch', 'post', 'put']);
        $paste = $this->Pastes->get($id);
        $paste = $this->Pastes->patchEntity($paste, $this->request->getData());
        if ($paste->user_id != $this->getUserId()) {
            $message = 'Unauthorized';
            $paste = '';
        } else {
            if ($this->Pastes->save($paste)) {
                $message = 'Saved';
            } else {
                $message = 'Error';
            }
        }
        $this->set([
            'message' => $message,
            'paste' => $paste,
        ]);
        $this->viewBuilder()->setOption('serialize', ['paste', 'message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    public function delete($id)
    {
        $this->request->allowMethod(['delete']);
        $paste = $this->Pastes->get($id);
        $message = 'Deleted';
        if ($paste->user_id != $this->getUserId()) {
            $message = 'Unauthorized';
        } elseif (!$this->Pastes->delete($paste)) {
            $message = 'Error';
        }
        $this->set('message', $message);
        $this->viewBuilder()->setOption('serialize', ['message']);
        $this->RequestHandler->renderAs($this, 'json');
    }

    protected function getUserId()
    {
        $jwt = $this->request->getQuery('token');
        $decoded = JWT::decode($jwt, Security::getSalt(), array('HS256'));
        return $decoded->sub;
    }
}