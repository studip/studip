<?php
/**
 * Icon class is used to create icon objects which can be rendered as
 * svg. Output will be html. Optionally, the icon can be rendered
 * as a css background.
 *
 * @author    Jan-Hendrik Willms <tleilax+studip@gmail.com>
 * @copyright Stud.IP Core Group
 * @license   GPL2 or any later version
 * @since     3.2
 */
class Icon
{
    const SVG = 1;
    const CSS_BACKGROUND = 4;
    const INPUT = 256;

    const DEFAULT_SIZE = 16;
    const DEFAULT_COLOR = 'blue';
    const DEFAULT_ROLE = 'clickable';

    const ROLE_INFO          = 'info';
    const ROLE_CLICKABLE     = 'clickable';
    const ROLE_ACCEPT        = 'accept';
    const ROLE_STATUS_GREEN  = 'status-green';
    const ROLE_INACTIVE      = 'inactive';
    const ROLE_NAVIGATION    = 'navigation';
    const ROLE_NEW           = 'new';
    const ROLE_ATTENTION     = 'attention';
    const ROLE_STATUS_RED    = 'status-red';
    const ROLE_INFO_ALT      = 'info_alt';
    const ROLE_SORT          = 'sort';
    const ROLE_STATUS_YELLOW = 'status-yellow';


    protected $shape;
    protected $role;
    protected $attributes = [];


    /**
     * This is the magical Role to Color mapping.
     */
    private static $roles_to_colors = [
        self::ROLE_INFO          => 'black',
        self::ROLE_CLICKABLE     => 'blue',
        self::ROLE_ACCEPT        => 'green',
        self::ROLE_STATUS_GREEN  => 'green',
        self::ROLE_INACTIVE      => 'grey',
        self::ROLE_NAVIGATION    => 'blue',
        self::ROLE_NEW           => 'red',
        self::ROLE_ATTENTION     => 'red',
        self::ROLE_STATUS_RED    => 'red',
        self::ROLE_INFO_ALT      => 'white',
        self::ROLE_SORT          => 'yellow',
        self::ROLE_STATUS_YELLOW => 'yellow'
    ];

    // return the color associated to a role
    private static function roleToColor($role)
    {
        if (!isset(self::$roles_to_colors[$role])) {
            throw new \InvalidArgumentException('Unknown role: "' . $role . '"');
        }
        return self::$roles_to_colors[$role];
    }

    // return the roles! associated to a color
    private static function colorToRoles($color)
    {
        static $colors_to_roles;

        if (!$colors_to_roles) {
            foreach (self::$roles_to_colors as $r => $c) {
                $colors_to_roles[$c][] = $r;
            }
        }

        if (!isset($colors_to_roles[$color])) {
            throw new \InvalidArgumentException('Unknown color: "' . $color . '"');
        }

        return $colors_to_roles[$color];
    }

    /**
     * Create a new Icon object.
     *
     * This is just a factory method. You could easily just call the
     * constructor instead.
     *
     * @param String $shape      Shape of the icon, may contain a mixed definition
     *                           like 'seminar+add'
     * @param String $role       Role of the icon, defaults to Icon::DEFAULT_ROLE
     * @param Array $attributes  Additional attributes like 'title';
     *                           only use semantic ones describing
     *                           this icon regardless of its later
     *                           rendering in a view
     * @return Icon object
     */
    public static function create($shape, $role = Icon::DEFAULT_ROLE, $attributes = [])
    {
        // $role may be omitted
        if (is_array($role)) {
            $attributes = $role;
            $role = Icon::DEFAULT_ROLE;
        }

        return new self($shape, $role, $attributes);
    }

    /**
     * Constructor of the object.
     *
     * @param String $shape      Shape of the icon, may contain a mixed definition
     *                           like 'seminar+add'
     * @param String $role       Role of the icon, defaults to Icon::DEFAULT_ROLE
     * @param Array $attributes  Additional attributes like 'title';
     *                           only use semantic ones describing
     *                           this icon regardless of its later
     *                           rendering in a view
     */
    public function __construct($shape, $role = Icon::DEFAULT_ROLE, array $attributes = [])
    {

        // only defined roles
        if (!isset(self::$roles_to_colors[$role])) {
            throw new \InvalidArgumentException('Creating an Icon without proper role: "' . $role . '"');
        }

        // only semantic attributes
        if ($non_semantic = array_filter(array_keys($attributes), function ($attr) {
            return !in_array($attr, ['title']);
        })) {
            // DEPRECATED
            // TODO starting with the v3.6 the following line should
            // be enabled to prevent non-semantic attributes in this position
            # throw new \InvalidArgumentException('Creating an Icon with non-semantic attributes:' . json_encode($non_semantic));
        }

        $this->shape      = $shape;
        $this->role       = $role;
        $this->attributes = $attributes;
    }

    /**
     * Returns the `shape` -- the string describing the shape of this instance.
     * @return String  the shape of this Icon
     */
    public function getShape()
    {
        return $this->shape;
    }

    /**
     * Returns the `role` -- the string describing the role of this instance.
     * @return String  the role of this Icon
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Returns the semantic `attributes` of this instance, e.g. the title of this Icon
     * @return Array  the semantic attribiutes of the Icon
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Function to be called whenever the object is converted to
     * string. Internally the same as calling Icon::asImg
     *
     * @return String representation
     */
    public function __toString()
    {
        return $this->asImg();
    }

    /**
     * Renders the icon inside an img html tag.
     *
     * @param int   $size             Optional; Defines the dimension in px of the rendered icon; FALSE prevents any
     *                                width or height attributes
     * @param Array $view_attributes  Optional; Additional attributes to pass
     *                                into the rendered output
     * @return String containing the html representation for the icon.
     */
    public function asImg($size = null, $view_attributes = [])
    {
        if (is_array($size)) {
            list($view_attributes, $size) = [$size, null];
        }
        return sprintf(
            '<img %s>',
            arrayToHtmlAttributes(
                $this->prepareHTMLAttributes($size, $view_attributes)
            )
        );
    }

    /**
     * Renders the icon inside an input html tag.
     *
     * @param int   $size             Optional; Defines the dimension in px of the rendered icon; FALSE prevents any
     *                                width or height attributes
     * @param Array $view_attributes  Optional; Additional attributes to pass
     *                                into the rendered output
     * @return String containing the html representation for the icon.
     */
    public function asInput($size = null, $view_attributes = [])
    {
        if (is_array($size)) {
            list($view_attributes, $size) = [$size, null];
        }
        return sprintf(
            '<input type="image" %s>',
            arrayToHtmlAttributes(
                $this->prepareHTMLAttributes($size, $view_attributes)
            )
        );
    }

    /**
     * Renders the icon as a set of css background rules.
     *
     * @param int $size  Optional; Defines the size in px of the rendered icon
     * @return String containing the html representation for css backgrounds
     */
    public function asCSS($size = null)
    {
        if (self::isStatic($this->shape)) {
            return sprintf(
                'background-image:url(%1$s);background-size:%2$upx %2$upx;',
                $this->shapeToPath($this->shape),
                $this->get_size($size)
            );
        }

        return sprintf(
            'background-image:url(%1$s);background-size:%2$upx %2$upx;',
            $this->get_asset_svg(),
            $this->get_size($size)
        );
    }

    /**
     * Returns a path to the SVG matching the icon.
     *
     * @return String containing the html representation for css backgrounds
     */
    public function asImagePath()
    {
        return $this->prepareHTMLAttributes(false, [])['src'];
    }

    /**
     * Returns a new Icon with a changed shape
     * @param mixed  $shape  New value of `shape`
     * @return Icon  A new Icon with a new `shape`
     */
    public function copyWithShape($shape)
    {
        $clone = clone $this;
        $clone->shape = $shape;
        return $clone;
    }

    /**
     * Returns a new Icon with a changed role
     * @param mixed  $role  New value of `role`
     * @return Icon  A new Icon with a new `role`
     */
    public function copyWithRole($role)
    {
        $clone = clone $this;
        $clone->role = $role;
        return $clone;
    }

    /**
     * Returns a new Icon with new attributes
     * @param mixed  $attributes  New value of `attributes`
     * @return Icon  A new Icon with a new `attributes`
     */
    public function copyWithAttributes($attributes)
    {
        $clone = clone $this;
        $clone->attributes = $attributes;
        return $clone;
    }

    /**
     * Prepares the html attributes for use assembling HTML attributes
     * from given shape, role, size, semantic and view attributes
     *
     * @param int   $size       Size of the icon
     * @param array $attributes Additional attributes
     * @return Array containing the merged attributes
     */
    private function prepareHTMLAttributes($size, $attributes)
    {
        $dimensions = [];
        if ($size !== false) {
            $size = $this->get_size($size);
            $dimensions = ['width'  => $size, 'height' => $size];
        }

        $result = array_merge($this->attributes, $attributes, $dimensions, [
            'src' => self::isStatic($this->shape) ? $this->shape : $this->get_asset_svg(),
            'alt' => $this->attributes['alt'] ?: $this->attributes['title'] ?: basename($this->shape)
        ]);

        $classNames = 'icon-role-' . $this->role;

        if (!self::isStatic($this->shape)) {
            $classNames .= ' icon-shape-' . $this->shape;
        }

        $result['class'] = isset($result['class']) ? $result['class'] . ' ' . $classNames : $classNames;

        return $result;
    }

    /**
     * Get the correct asset for an SVG icon.
     *
     * @return String containing the url of the corresponding asset
     */
    protected function get_asset_svg()
    {
        return Assets::url('images/icons/' . self::roleToColor($this->role) . '/' . $this->shapeToPath($this->shape) . '.svg');
    }

    /**
     * Get the size of the icon. If a size was passed as a parameter and
     * inside the attributes array during icon construction, the size from
     * the attributes will be used.
     *
     * @param int $size  size of the icon
     * @return int Size of the icon in pixels
     */
    protected function get_size($size)
    {
        $size = $size ?: Icon::DEFAULT_SIZE;
        if (isset($this->attributes['size'])) {
            list($size, $temp) = explode('@', $this->attributes['size'], 2);
            unset($this->attributes['size']);
        }
        return (int)$size;
    }

    // an icon is static if it starts with 'http'
    private static function isStatic($shape)
    {
        return mb_strpos($shape, 'http') === 0;
    }

    // transforms a shape w/ possible additions (`shape+addition`) to a path `(addition/)?shape`
    private function shapeToPath()
    {
        return self::isStatic($this->shape)
            ? $this->shape :
            join('/', array_reverse(explode('+', preg_replace('/\.svg$/', '', $this->shape))));
    }
}
