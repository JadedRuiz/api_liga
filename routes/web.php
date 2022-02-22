<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

    $router->get("categorias","Controller@catalogoCategorias");
    $router->get("temporadaActual","Controller@temporadaActual");
    $router->get("obtenerContra/{code}", "Controller@decode_json");
    $router->get("obtenerEquipo/{usuario_id}", "Controller@obtenerEquipo");

    $router->group(['prefix' => 'registro'], function () use ($router) {
        $router->post("solicitud","LoginController@registro");
        $router->post("login","LoginController@login");
        $router->post("recuperarContra","LoginController@recuperarContra");$router->get("enviarContra/{id_usuario}","LoginController@enviarContra");
    });

    $router->group(['prefix' => 'inicio'], function () use ($router) {
        $router->get("obtenerDatos/{id_usuario}","InicioController@obtenerDatos");
        $router->get("obtenerDatosPorId/{inscripcion_id}","InicioController@obtenerDatosPorId");
        $router->post("EditarDatos","InicioController@EditarDatos");
    });
    $router->group(['prefix' => 'inscripcion'], function () use ($router) {
        $router->get("obtenerSolicitudes","InscripcionController@obtenerSolicitudesDeInscripciones");
        $router->get("obtenerInscripciones","InscripcionController@obtenerInscripciones");
        $router->get("obtenerRecibo/{id_inscripcion}","InscripcionController@obtenerReciboPorId");
        $router->get("validarSolicitud/{id_inscripcion}","InscripcionController@validarSolicitudDeInscripcion");
        $router->get("habilitarDeshabilitarEquipo/{inscripcion_id}/{tipo}","InscripcionController@habilitarDeshabilitarEquipo");
    });
    $router->group(['prefix' => 'jugadores'], function () use ($router) {
        $router->post("validarCurp","JugadoresController@validarCurp");
        $router->post("altaJugador","JugadoresController@altaJugador");
        $router->get("bajaJugador/{id_jugador}","JugadoresController@bajaJugador");
        $router->get("obtenerJugadoresAdmin","JugadoresController@obtenerJugadoresAdmin");
        $router->get("obtenerEquipos","JugadoresController@obtenerEquipos");
        $router->get("obtenerJugadorPorId/{id_jugador}","JugadoresController@obtenerJugadorPorId");
        $router->post("busquedaJugadores","JugadoresController@busquedaJugadores");
        $router->post("altaJugadorAdmin","JugadoresController@altaJugadorAdmin");
        $router->post("altaJugadorAEquipo","JugadoresController@altaJugadorAEquipo");
    });
    $router->group(['prefix' => 'reportes'], function () use ($router) {
        $router->post("obtenerReporte","ReporteController@obtenerPDF");
    });
    $router->group(['prefix' => 'admin'], function () use ($router) {
        $router->post("obtenerUsuariosTemp","AdminController@obtenerUsuariosTemp");
    $router->get("getContra/{id_usuario}", "AdminController@getContra");
        
    });
});
