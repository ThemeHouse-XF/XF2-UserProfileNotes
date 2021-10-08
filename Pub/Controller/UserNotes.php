<?php

namespace ThemeHouse\UserNotes\Pub\Controller;

use ThemeHouse\UserNotes\Entity\UserNote;
use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

/**
 * Class UserNotes
 * @package ThemeHouse\UserNotes\Pub\Controller
 */
class UserNotes extends AbstractController
{
    /**
     * @var
     */
    protected $user;

    /**
     * @param $action
     * @param ParameterBag $params
     * @return
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function preDispatchController($action, ParameterBag $params)
    {
        if (!\XF::visitor()->hasPermission('th_usernotes', 'view')) {
            throw $this->exception($this->noPermission());
        }

        $this->user = $this->assertRecordExists('XF:User', $params->user_id);
        return;
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     */
    public function actionIndex(ParameterBag $params)
    {
        $user = $this->user;

        $page = $this->filterPage();
        $perPage = 25;

        $noteFinder = $this->finder('ThemeHouse\UserNotes:UserNote')
            ->where('profile_user_id', '=', $user->user_id)
            ->order('post_date', 'DESC')
            ->limitByPage($page, $perPage);

        $viewParams = [
            'user' => $user,
            'notes' => $noteFinder->fetch(),
            'page' => $page,
            'perPage' => $perPage,
            'total' => $noteFinder->total()
        ];

        return $this->view('ThemeHouse\UserNotes:UserNotes', 'th_usernotes_list', $viewParams);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\PrintableException
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionSave(ParameterBag $params)
    {
        if ($params->user_note_id) {
            /** @var UserNote $userNote */
            $userNote = $this->assertUserNoteExists($params->user_note_id);
            if (!$userNote->canEdit()) {
                return $this->noPermission();
            }
        } else {
            if (!\XF::visitor()->hasPermission('th_usernotes', 'add')) {
                return $this->noPermission();
            }

            /** @var UserNote $userNote */
            $userNote = $this->em()->create('ThemeHouse\UserNotes:UserNote');
            $userNote->profile_user_id = $params->user_id;
        }

        $this->userNoteSaveProcess($userNote)->run();


        if ($this->filter('_xfWithData', 'bool') && $this->filter('_xfInlineEdit', 'bool')) {
            $viewParams = [
                'note' => $userNote,
            ];
            $reply = $this->view('ThemeHouse\UserNotes:UserNote\Edit', 'th_usernotes_edit_new_note', $viewParams);
            $reply->setJsonParam('message', \XF::phrase('your_changes_have_been_saved'));
            return $reply;
        } else {
            return $this->redirect($this->buildLink('members', $this->user) . '#th-usernotes');
        }
    }

    /**
     * @param UserNote $note
     * @return \XF\Mvc\FormAction
     */
    protected function userNoteSaveProcess(UserNote $note)
    {
        $form = $this->formAction();

        $input = [];

        if ($note->isInsert()) {
            $input += [
                'username' => \XF::visitor()->username,
                'user_id' => \XF::visitor()->user_id
            ];
        } else {
            $input += [
                'last_edit_date' => \XF::$time
            ];
        }

        /** @var \XF\ControllerPlugin\Editor $editor */
        $editor = $this->plugin('XF:Editor');
        $input['message'] = $editor->fromInput('message');

        $form->basicEntitySave($note, $input);

        return $form;
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionEdit(ParameterBag $params)
    {
        /** @var UserNote $userNote */
        $userNote = $this->assertUserNoteExists($params->user_note_id);

        if (!$userNote->canEdit()) {
            return $this->noPermission();
        }


        $viewParams = [
            'note' => $userNote,
            'profileUser' => $userNote->ProfileUser,

            'quickEdit' => $this->filter('_xfWithData', 'bool')
        ];
        return $this->view('ThemeHouse\UserNotes:UserNote\Edit', 'th_usernotes_edit', $viewParams);
    }

    /**
     * @param ParameterBag $params
     * @return \XF\Mvc\Reply\Error|\XF\Mvc\Reply\Redirect|\XF\Mvc\Reply\View
     * @throws \XF\Mvc\Reply\Exception
     */
    public function actionDelete(ParameterBag $params)
    {
        /** @var UserNote $userNote */
        $userNote = $this->assertUserNoteExists($params->user_note_id);

        /** @var \XF\ControllerPlugin\Delete $plugin */
        $plugin = $this->plugin('XF:Delete');

        return $plugin->actionDelete(
            $userNote,
            $this->buildLink('members/th-usernotes/delete', $userNote),
            $this->buildLink('members', $userNote->ProfileUser) . '#th-usernotes',
            $this->buildLink('members', $userNote->ProfileUser) . '#th-usernotes',
            \XF::phrase('th_usernotes_note')
        );
    }

    /**
     * @param $id
     * @param null $with
     * @param null $phraseKey
     * @return UserNote
     * @throws \XF\Mvc\Reply\Exception
     */
    protected function assertUserNoteExists($id, $with = null, $phraseKey = null) {
        /** @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->assertRecordExists('ThemeHouse\UserNotes:UserNote', $id, $with, $phraseKey);
    }
}