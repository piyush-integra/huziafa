<?php

namespace WPML\Core;

use \WPML\Core\Twig\Environment;
use \WPML\Core\Twig\Error\LoaderError;
use \WPML\Core\Twig\Error\RuntimeError;
use \WPML\Core\Twig\Markup;
use \WPML\Core\Twig\Sandbox\SecurityError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedTagError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFilterError;
use \WPML\Core\Twig\Sandbox\SecurityNotAllowedFunctionError;
use \WPML\Core\Twig\Source;
use \WPML\Core\Twig\Template;

/* products.twig */
class __TwigTemplate_3a4bab3b3288a9c6bf21a1a78a2fc3fc52db8db3219fba7a1168f3fa5186f72e extends \WPML\Core\Twig\Template
{
    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        // line 1
        echo "<div class=\"wcml-section wc-products-section\">
    <div class=\"wcml-section-header\">
        <h3>
            ";
        // line 4
        echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "products_missing", []), "html", null, true);
        echo "
        </h3>
    </div>
    <div class=\"wcml-section-content\">
        <ul class=\"wcml-status-list wcml-plugins-status-list\">
            ";
        // line 9
        if (twig_test_empty(($context["products"] ?? null))) {
            // line 10
            echo "                <li>
                    <i class=\"otgs-ico-ok\"></i>
                    ";
            // line 12
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "not_to_trnsl", []), "html", null, true);
            echo "
                </li>
            ";
        } else {
            // line 15
            echo "                ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(($context["products"] ?? null));
            foreach ($context['_seq'] as $context["_key"] => $context["product"]) {
                // line 16
                echo "                    <li>
                        <i class=\"otgs-ico-warning\"></i>
                        <span class=\"wpml-title-flag\">
                            ";
                // line 19
                echo $this->getAttribute($context["product"], "flag", []);
                echo "
                        </span>
                        ";
                // line 21
                if (($this->getAttribute($context["product"], "count", []) == 1)) {
                    // line 22
                    echo "                            ";
                    echo \WPML\Core\twig_escape_filter($this->env, sprintf($this->getAttribute(($context["strings"] ?? null), "miss_trnsl_one", []), $this->getAttribute($context["product"], "count", []), $this->getAttribute($context["product"], "display_name", [])), "html", null, true);
                    echo "
                        ";
                } else {
                    // line 24
                    echo "                            ";
                    echo \WPML\Core\twig_escape_filter($this->env, sprintf($this->getAttribute(($context["strings"] ?? null), "miss_trnsl_more", []), $this->getAttribute($context["product"], "count", []), $this->getAttribute($context["product"], "display_name", [])), "html", null, true);
                    echo "
                        ";
                }
                // line 26
                echo "                    </li>
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['product'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 28
            echo "
                <p>
                    <a class=\"button-secondary aligncenter\" href=\"";
            // line 30
            echo \WPML\Core\twig_escape_filter($this->env, ($context["trnsl_link"] ?? null), "html", null, true);
            echo "\">
                        ";
            // line 31
            echo \WPML\Core\twig_escape_filter($this->env, $this->getAttribute(($context["strings"] ?? null), "transl", []), "html", null, true);
            echo "
                    </a>
                </p>
            ";
        }
        // line 35
        echo "        </ul>
    </div>
</div>";
    }

    public function getTemplateName()
    {
        return "products.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  108 => 35,  101 => 31,  97 => 30,  93 => 28,  86 => 26,  80 => 24,  74 => 22,  72 => 21,  67 => 19,  62 => 16,  57 => 15,  51 => 12,  47 => 10,  45 => 9,  37 => 4,  32 => 1,);
    }

    /** @deprecated since 1.27 (to be removed in 2.0). Use getSourceContext() instead */
    public function getSource()
    {
        @trigger_error('The '.__METHOD__.' method is deprecated since version 1.27 and will be removed in 2.0. Use getSourceContext() instead.', E_USER_DEPRECATED);

        return $this->getSourceContext()->getCode();
    }

    public function getSourceContext()
    {
        return new Source("", "products.twig", "/home/forge/staging.alhuzaifa.slz.io/wp-content/plugins/woocommerce-multilingual/templates/status/products.twig");
    }
}
