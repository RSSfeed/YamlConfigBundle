<?php

namespace ITF\YamlConfigBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        if (!$this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            throw new Exception("You need to be SUPER_ADMIN to edit this configuration.");
        }

        $yml = $this->get('itf.yconf');

        if ($request->isMethod('POST')) {
            $array = $request->get('array');
            if ($yml->saveArray($array)) {
                $this->get('session')->getFlashBag()->add('notice', 'Saved!');
            }
        }

        return $this->render('YamlConfigBundle:Default:edit.html.twig', array(
            'file_name' => $yml->getConfigFileName(),
            'array' => $yml->getAll()
        ));
    }
}
