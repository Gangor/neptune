<?php

class Router
{
    static public function parse(string $url, $request)
    {
        $request->controller    = Router::get( $url, 1, CONTROLLER_DEF );
        $request->action        = Router::get( $url, 2, ACTION_DEF );
        $request->params        = Router::param( $url );

        for ( $i = 0; $i < sizeof( $request->params ); $i++ )
        {
            if ( is_bool( $request->params[ $i ] ) )
                $request->params[ $i ] = boolval( $request->params[ $i ] );
            elseif ( is_numeric( $request->params[ $i ] ) )
                $request->params[ $i ] = intval( $request->params[ $i ] );
            elseif ( is_float( $request->params[ $i ] ) )
                $request->params[ $i ] = floatval( $request->params[ $i ] );
        }
    }

    static public function get( string $url, int $index, $default )
    {
        $url = trim($url);

        if (strpos($url, '?' ))
            $explode_url = explode( '/', substr( $url, 0, strpos($url, '?' )));
        else
            $explode_url = explode( '/', $url);

        if ( isset( $explode_url[ $index ] ) 
            && !empty( $explode_url[ $index ] ) )
            return strtolower( $explode_url[ $index ] );

        return $default;
    }

    static public function param( string $url )
    {
        $url = trim( $url );
        $params = array();

        if ( strpos( $url, '?' ) )
        {
            $pos = strpos( $url, '?' ) + 1;
            $len = strlen( $url );
            $params = explode( '&', substr( $url, $pos, $len ));

            for ( $i = 0; $i < sizeof( $params ); $i++ )
            {
                $explodes = explode( '=', $params[$i] );
                $params[$i] = $explodes[1];
            }
        }
        else
        {
            $params = explode( '/', $url );
        }

        if ( sizeof( $params ) > 0 ) 
            return array_slice( $params, 0 );

        return array();
    }

    static public function redirect( string $url )
    {
        header( 'Location: /'. $url );
        exit( 0 );
    }

    static public function redirectWithParams( string $url, array $params )
    {
        foreach( $params as $param ) {
            $url .= '/'.$param;
        }

        header( 'Location: '. $url );
        exit( 0 );
    }

    static public function redirectLocal( string $controller, string $action )
    {
        header( 'Location: /'. $controller .'/'. $action );
        exit( 0 );
    }

    static public function redirectLocalWithParams( string $controller, string $action, array $params )
    {
        $url = './'.$controller.'/'.$action;

        foreach( $params as $param ) {
            $url .= '/'.$param;
        }
        header( 'Location: '. $url );
        exit( 0 );
    }
}
?>