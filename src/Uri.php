<?php
/**
 * Created by PhpStorm.
 * User: chendong
 * Date: 16/7/17
 * Time: 12:37
 */

namespace cdcchen\psr7;


use Psr\Http\Message\UriInterface;

/**
 * Class Uri
 * @package cdcchen\psr7
 */
class Uri implements UriInterface
{
    const HTTP_DEFAULT_HOST = 'localhost';

    /**
     * @var string
     */
    private $scheme = '';
    /**
     * @var string
     */
    private $host = '';
    /**
     * @var int
     */
    private $port;
    /**
     * @var string
     */
    private $path = '';
    /**
     * @var string
     */
    private $query = '';
    /**
     * @var string
     */
    private $userInfo = '';
    /**
     * @var string
     */
    private $fragment = '';

    /**
     * @var array
     */
    private static $defaultPorts = [
        'http' => 80,
        'https' => 443,
        'ftp' => 21,
        'gopher' => 70,
        'nntp' => 119,
        'news' => 119,
        'telnet' => 23,
        'tn3270' => 23,
        'imap' => 143,
        'pop' => 110,
        'ldap' => 389,
    ];

    /**
     * @var string
     */
    private static $charUnreserved = 'a-zA-Z0-9_\-\.~';
    /**
     * @var string
     */
    private static $charSubDelimiters = '!\$&\'\(\)\*\+,;=';
    /**
     * @var array
     */
    private static $replaceQuery = ['=' => '%3D', '&' => '%26'];


    /**
     * Uri constructor.
     * @param string $uri
     */
    public function __construct($uri = '')
    {
        if (!empty($uri)) {
            $parts = parse_url($uri);
            if ($parts === false) {
                throw new \InvalidArgumentException("$uri is not a valid URI.");
            }
            $this->applyParts($parts);
        }
    }

    /**
     * @param string $str
     * @return static
     */
    public static function createFromString($str)
    {
        $parts = parse_url($str);
        if ($parts === false) {
            throw new \InvalidArgumentException("{$str} is not a valid URI string.");
        }

        return static::createFromParts($parts);
    }

    /**
     * @param array $parts
     * @return static
     */
    public static function createFromParts($parts)
    {
        $uri = new static();
        $uri->applyParts($parts);
        $uri->validateState();

        return $uri;
    }

    /**
     * @param array $parts
     */
    private function applyParts(array $parts)
    {
        if (!empty($parts['scheme'])) {
            $this->scheme = $this->filterScheme($parts['scheme']);
        }
        if (!empty($parts['user'])) {
            $this->userInfo = $parts['user'];
        }
        if (isset($parts['pass'])) {
            $this->userInfo .= ':' . $parts['pass'];
        }
        if (!empty($parts['host'])) {
            $this->host = $this->filterHost($parts['host']);
        }
        if (!empty($parts['port'])) {
            $this->port = $this->filterPort($parts['port']);
        }
        if (!empty($parts['path'])) {
            $this->path = $this->filterPath($parts['path']);
        }
        if (!empty($parts['query'])) {
            $this->query = $this->filterQuery($parts['query']);
        }
        if (!empty($parts['fragment'])) {
            $this->fragment = $this->filterFragment($parts['fragment']);
        }

        $this->removeDefaultPort();
    }

    /**
     * @param string $scheme
     * @param string $authority
     * @param string $path
     * @param string $query
     * @param string $fragment
     * @return string
     */
    public static function buildUriString($scheme, $authority, $path, $query, $fragment)
    {
        $uri = '';

        if (!empty($scheme)) {
            $uri .= $scheme . ':';
        }
        if (!empty($authority) || $scheme === 'file') {
            $uri .= '//' . $authority;
        }

        $uri .= $path;

        if (!empty($query)) {
            $uri .= '?' . $query;
        }
        if (!empty($fragment)) {
            $uri .= '#' . $fragment;
        }

        return $uri;
    }

    /**
     * @return string
     */
    public function getScheme()
    {
        return $this->scheme;
    }

    /**
     * @return string
     */
    public function getAuthority()
    {
        $authority = $this->host;
        if (!empty($this->userInfo)) {
            $authority = $this->userInfo . '@' . $authority;
        }
        if ($this->port !== null) {
            $authority .= ':' . $this->port;
        }

        return $authority;
    }

    /**
     * @return string
     */
    public function getUserInfo()
    {
        return $this->userInfo;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->fragment;
    }

    /**
     * Whether the URI has the default port of the current scheme.
     *
     * `Psr\Http\Message\UriInterface::getPort` may return null or the standard port. This method can be used
     * independently of the implementation.
     *
     * @param UriInterface $uri
     *
     * @return bool
     */
    public static function isDefaultPort(UriInterface $uri)
    {
        return $uri->getPort() === null
            || (isset(self::$defaultPorts[$uri->getScheme()]) && $uri->getPort() === self::$defaultPorts[$uri->getScheme()]);
    }

    /**
     * Whether the URI is absolute, i.e. it has a scheme.
     *
     * An instance of UriInterface can either be an absolute URI or a relative reference. This method returns true
     * if it is the former. An absolute URI has a scheme. A relative reference is used to express a URI relative
     * to another URI, the base URI. Relative references can be divided into several forms:
     * - network-path references, e.g. '//example.com/path'
     * - absolute-path references, e.g. '/path'
     * - relative-path references, e.g. 'subpath'
     *
     * @param UriInterface $uri
     *
     * @return bool
     * @see Uri::isNetworkPathReference
     * @see Uri::isAbsolutePathReference
     * @see Uri::isRelativePathReference
     * @link https://tools.ietf.org/html/rfc3986#section-4
     */
    public static function isAbsolute(UriInterface $uri)
    {
        return $uri->getScheme() !== '';
    }

    /**
     * Whether the URI is a network-path reference.
     *
     * A relative reference that begins with two slash characters is termed an network-path reference.
     *
     * @param UriInterface $uri
     *
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public static function isNetworkPathReference(UriInterface $uri)
    {
        return $uri->getScheme() === '' && $uri->getAuthority() !== '';
    }

    /**
     * Whether the URI is a absolute-path reference.
     *
     * A relative reference that begins with a single slash character is termed an absolute-path reference.
     *
     * @param UriInterface $uri
     *
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public static function isAbsolutePathReference(UriInterface $uri)
    {
        return $uri->getScheme() === '' && $uri->getAuthority() === ''
            && isset($uri->getPath()[0]) && $uri->getPath()[0] === '/';
    }

    /**
     * Whether the URI is a relative-path reference.
     *
     * A relative reference that does not begin with a slash character is termed a relative-path reference.
     *
     * @param UriInterface $uri
     *
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.2
     */
    public static function isRelativePathReference(UriInterface $uri)
    {
        return $uri->getScheme() === '' && $uri->getAuthority() === ''
            && (!isset($uri->getPath()[0]) || $uri->getPath()[0] !== '/');
    }

    /**
     * Whether the URI is a same-document reference.
     *
     * A same-document reference refers to a URI that is, aside from its fragment
     * component, identical to the base URI. When no base URI is given, only an empty
     * URI reference (apart from its fragment) is considered a same-document reference.
     *
     * @param UriInterface $uri The URI to check
     * @param UriInterface|null $base An optional base URI to compare against
     *
     * @return bool
     * @link https://tools.ietf.org/html/rfc3986#section-4.4
     */
    public static function isSameDocumentReference(UriInterface $uri, UriInterface $base = null)
    {
        if ($base !== null) {
            $uri = UriResolver::resolve($base, $uri);
            return ($uri->getScheme() === $base->getScheme())
                && ($uri->getAuthority() === $base->getAuthority())
                && ($uri->getPath() === $base->getPath())
                && ($uri->getQuery() === $base->getQuery());
        }

        return $uri->getScheme() === '' && $uri->getAuthority() === '' && $uri->getPath() === '' && $uri->getQuery() === '';
    }

    /**
     * @param UriInterface $uri
     * @param string $key
     * @return UriInterface
     */
    public static function withoutQueryValue(UriInterface $uri, $key)
    {
        $current = $uri->getQuery();
        if ($current === '') {
            return $uri;
        }

        $decodedKey = rawurldecode($key);
        $result = array_filter(explode('&', $current), function ($part) use ($decodedKey) {
            return rawurldecode(explode('=', $part)[0]) !== $decodedKey;
        });

        return $uri->withQuery(implode('&', $result));
    }

    /**
     * @param UriInterface $uri
     * @param string $key
     * @param string $value
     * @return UriInterface
     */
    public static function withQueryValue(UriInterface $uri, $key, $value)
    {
        $current = $uri->getQuery();
        if ($current === '') {
            $result = [];
        } else {
            $decodedKey = rawurldecode($key);
            $result = array_filter(explode('&', $current), function ($part) use ($decodedKey) {
                return rawurldecode(explode('=', $part)[0]) !== $decodedKey;
            });
        }

        // Query string separators ("=", "&") within the key or value need to be encoded
        // (while preventing double-encoding) before setting the query string. All other
        // chars that need percent-encoding will be encoded by withQuery().
        $key = strtr($key, self::$replaceQuery);
        if ($value !== null) {
            $result[] = $key . '=' . rawurlencode($value);
        } else {
            $result[] = $key;
        }
        return $uri->withQuery(implode('&', $result));
    }

    /**
     * @param string $scheme
     * @return static
     */
    public function withScheme($scheme)
    {
        $scheme = $this->filterScheme($scheme);
        if ($this->scheme === $scheme) {
            return $this;
        }

        $new = clone $this;
        $new->scheme = $scheme;
        $new->removeDefaultPort();
        $new->validateState();

        return $new;
    }

    /**
     * @param string $user
     * @param null|string $password
     * @return $this
     */
    public function withUserInfo($user, $password = null)
    {
        $info = $user;
        if ($password != '') {
            $info .= ':' . $password;
        }
        if ($this->userInfo === $info) {
            return $this;
        }

        $new = clone $this;
        $new->userInfo = $info;
        $new->validateState();

        return $new;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function withHost($host)
    {
        $host = $this->filterHost($host);
        if ($this->host === $host) {
            return $this;
        }

        $new = clone $this;
        $new->host = $host;
        $new->validateState();

        return $new;
    }

    /**
     * @param int|null $port
     * @return $this
     */
    public function withPort($port)
    {
        $port = $this->filterPort($port);
        if ($this->port === $port) {
            return $this;
        }

        $new = clone $this;
        $new->port = $port;
        $new->removeDefaultPort();
        $new->validateState();

        return $new;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function withPath($path)
    {
        $path = $this->filterPath($path);
        if ($this->path === $path) {
            return $this;
        }

        $new = clone $this;
        $new->path = $path;
        $new->validateState();

        return $new;
    }

    /**
     * @param string $query
     * @return $this
     */
    public function withQuery($query)
    {
        $query = $this->filterQuery($query);
        if ($this->query === $query) {
            return $this;
        }

        $new = clone $this;
        $new->query = $query;

        return $new;
    }

    /**
     * @param string $fragment
     * @return $this
     */
    public function withFragment($fragment)
    {
        $fragment = $this->filterFragment($fragment);
        if ($this->fragment === $fragment) {
            return $this;
        }

        $new = clone $this;
        $new->fragment = $fragment;

        return $new;
    }

    /**
     * @param string $scheme
     * @return string
     */
    private function filterScheme($scheme)
    {
        if (!is_string($scheme)) {
            throw new \InvalidArgumentException('Scheme must be a string');
        }
        return strtolower($scheme);
    }

    /**
     * @param string $host
     * @return string
     */
    private function filterHost($host)
    {
        if (!is_string($host)) {
            throw new \InvalidArgumentException('Host must be a string');
        }
        return strtolower($host);
    }

    /**
     * @param int $port
     * @return int|null
     */
    private function filterPort($port)
    {
        if ($port === null) {
            return null;
        }
        $port = (int)$port;
        if ($port < 1 || $port > 0xffff) {
            throw new \InvalidArgumentException(sprintf('Invalid port: %d. Must be between 1 and 65535', $port));
        }

        return $port;
    }

    /**
     * Remove port value if it is default port.
     */
    private function removeDefaultPort()
    {
        if ($this->port !== null && self::isDefaultPort($this)) {
            $this->port = null;
        }
    }

    /**
     * @param string $path
     * @return mixed
     */
    private function filterPath($path)
    {
        if (!is_string($path)) {
            throw new \InvalidArgumentException('Path must be a string');
        }

        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelimiters . '%:@\/]++|%(?![A-Fa-f0-9]{2}))/',
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $path
        );
    }

    /**
     * @param $str
     * @return mixed
     */
    private function filterQuery($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Query and fragment must be a string');
        }
        parse_str($str, $queryParams);
        return http_build_query($queryParams, null, '&', PHP_QUERY_RFC3986);
    }

    /**
     * @param $str
     * @return mixed
     */
    private function filterFragment($str)
    {
        if (!is_string($str)) {
            throw new \InvalidArgumentException('Query and fragment must be a string');
        }
        return preg_replace_callback(
            '/(?:[^' . self::$charUnreserved . self::$charSubDelimiters . '%:@\/\?]++|%(?![A-Fa-f0-9]{2}))/',
            function (array $matches) {
                return rawurlencode($matches[0]);
            },
            $str
        );
    }

    /**
     * Validate state
     */
    private function validateState()
    {
        if ($this->host === '' && ($this->scheme === 'http' || $this->scheme === 'https')) {
            $this->host = self::HTTP_DEFAULT_HOST;
        }

        if ($this->getAuthority() === '') {
            if (strpos($this->path, '//') === 0) {
                throw new \InvalidArgumentException('The path of a URI without an authority must not start with two slashes "//"');
            }
            if ($this->scheme === '' && strpos(explode('/', $this->path, 2)[0], ':') !== false) {
                throw new \InvalidArgumentException('A relative URI must not have a path beginning with a segment containing a colon');
            }
        } elseif (isset($this->path[0]) && $this->path[0] !== '/') {
            throw new \InvalidArgumentException('The path of a URI with an authority must start with a slash "/" or be empty');
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return static::buildUriString(
            $this->getScheme(),
            $this->getAuthority(),
            $this->getPath(),
            $this->getQuery(),
            $this->getFragment()
        );
    }
}