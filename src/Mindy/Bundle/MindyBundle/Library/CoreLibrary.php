<?php
/**
 * Author: Falaleev Maxim
 * Email: max@studio107.ru
 * Company: http://en.studio107.ru
 * Date: 18/02/16
 * Time: 12:26
 */

namespace Mindy\Bundle\MindyBundle\Library;

use Mindy\Bundle\MindyBundle\MindyBundle;
use Mindy\Template\Expression\ArrayExpression;
use Mindy\Template\Expression\AttributeExpression;
use Mindy\Template\Library;
use Mindy\Template\Token;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Security\Core\Role\SwitchUserRole;

class CoreLibrary extends Library implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /**
     * @return array
     */
    public function getHelpers()
    {
        return [
            'switched_user' => function () {
                $authChecker = $this->container->get('security.authorization_checker');
                $tokenStorage = $this->container->get('security.token_storage');

                if ($authChecker->isGranted('ROLE_PREVIOUS_ADMIN')) {
                    foreach ($tokenStorage->getToken()->getRoles() as $role) {
                        if ($role instanceof SwitchUserRole) {
                            return $role->getSource()->getUser();
                        }
                    }
                }
                return null;
            },
            'is_granted' => function ($attributes, $object = null) {
                if (!$this->container->has('security.authorization_checker')) {
                    throw new \LogicException('The SecurityBundle is not registered in your application.');
                }

                return $this->container->get('security.authorization_checker')->isGranted($attributes, $object);
            },
            'path' => function ($route, array $parameters = array()) {
                return $this->container->get('router')->generate($route, $parameters);
            },
            'url' => function ($route, array $parameters = array()) {
                return $this->container->get('router')->generate($route, $parameters);
            },
            'rand' => function ($min, $max) {
                return rand($min, $max);
            },
            'd' => function () {
                dump(func_get_args());
                die();
            },
            't' => function ($id, array $parameters = array(), $domain = null, $locale = null) {
                return $this->container->get('translator')->trans($id, $parameters, $domain, $locale);
            },
            'trans' => function ($id, array $parameters = array(), $domain = null, $locale = null) {
                return $this->container->get('translator')->trans($id, $parameters, $domain, $locale);
            },
            'transChoice' => function ($id, $number, array $parameters = array(), $domain = null, $locale = null) {
                return $this->container->get('translator')->transChoice($id, $number, $parameters, $domain, $locale);
            },
        ];
    }

    /**
     * @return array
     */
    public function getTags()
    {
        return [
            'url' => 'parseUrl'
        ];
    }

    public function parseUrl($token)
    {
        $name = null;
        $params = array();
        $route = $this->parser->parseExpression();
        while (
            (
                $this->stream->test(Token::NAME) ||
                $this->stream->test(Token::NUMBER) ||
                $this->stream->test(Token::STRING)
            ) && !$this->stream->test(Token::BLOCK_END)
        ) {

            if ($this->stream->consume(Token::NAME, 'with')) {
                if ($this->stream->look()->test(Token::OPERATOR, '=')) {
                    $this->stream->expect(Token::OPERATOR, '[');
                    $params = $this->parser->parseArrayExpression();
                    $this->stream->expect(Token::OPERATOR, ']');
                } else {
                    $params = $this->parser->parseExpression();
                }
            } else if ($this->stream->test(Token::NAME) && $this->stream->look()->test(Token::OPERATOR, '=')) {
                $key = $this->parser->parseName()->getValue();
                $this->stream->next();
                $params[$key] = $this->parser->parseExpression();
            } else if ($this->stream->test(Token::NAME, 'as')) {
                $this->stream->next();
                $name = $this->parser->parseName()->getValue();
            } else if ($this->stream->test(Token::NAME)) {
                $expression = $this->parser->parseExpression();
                if (
                    $expression instanceof ArrayExpression ||
                    $expression instanceof AttributeExpression
                ) {
                    $params = $expression;
                    break;
                } else {
                    $params[] = $expression;
                }
            } else {
                $params[] = $this->parser->parseExpression();
            }
        }

        $this->stream->expect(Token::BLOCK_END);
        return new UrlNode($token->getLine(), $route, $params, $name);
    }
}