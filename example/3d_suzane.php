<?php 
if (!defined('DS')) { define('DS', DIRECTORY_SEPARATOR); }
/**
 *---------------------------------------------------------------
 * Autoloader / Compser
 *---------------------------------------------------------------
 *
 * We need to access our dependencies & autloader..
 */
require __DIR__ . DS . '..' . DS . 'vendor' . DS . 'autoload.php';

use glm\vec3;

use PGF\{
	Window, 
    Common\FrameLimiter,
    Texture\Texture,
    Mesh\TexturedMesh,
    Shaders\Simple3DShader,
    Camera\PerspectiveCamera,

    Component\Transform3D
};

$window = new Window;

// configure the window
$window->setHint(GLFW_CONTEXT_VERSION_MAJOR, 3);
$window->setHint(GLFW_CONTEXT_VERSION_MINOR, 3);
$window->setHint(GLFW_OPENGL_PROFILE, GLFW_OPENGL_CORE_PROFILE);
$window->setHint(GLFW_OPENGL_FORWARD_COMPAT, GL_TRUE);

// open it
$window->open('3D Suzane');

// enable vsync
$window->setSwapInterval(1);

// create frame limiter
$fl = new FrameLimiter();

/**
 * Create a camera
 */
$camera = new PerspectiveCamera(new vec3(0.0, 0.0, 7.0));

/**
 * Create basic 3D Shader
 */
$shader = new Simple3DShader();
$shader->use();
$shader->setProjectionMatrx(\glm\value_ptr($camera->getProjectionMatrx()));
$shader->setViewMatrx(\glm\value_ptr($camera->getViewMatrix()));
$shader->setViewPosition($camera->position);
$shader->setLightPosition(\glm\vec3(20, 1, 50));

// set the color 
$shader->uniform1i('has_diffuse_texture', 0);
$shader->uniform3f('diffuse_color', 0.91, 0.28, 0.22);

// set specular
$shader->uniform1i('has_specular_texture', 0);
$shader->uniform3f('specular_color', 0.71, 0.28, 0.22);

$shader->uniform1f('shininess', 32.0);
$shader->uniform1f('specular_strength', 2);

/**
 * Get Suzane
 */
$suzane = new TexturedMesh(require __DIR__ . '/meshes/suzane.php');

/**
 * create the transforms
 */
$transform1 = new Transform3D(
	new vec3(0.0, 0.0, 0.0),
	new vec3(1.0, 1.0, 1.0),
	new vec3(90.0, 0.0, 0.0)
);

// enable deph test
glEnable(GL_DEPTH_TEST);

/**
 * Main loop
 */
while (!$window->shouldClose())
{
    $fl->start();
	$window->clearColor(0, 0, 0, 1);
	$window->clear(GL_COLOR_BUFFER_BIT | GL_DEPTH_BUFFER_BIT);

    // set rendering mode
    if ($window->getKeyState(GLFW_KEY_1) === GLFW_PRESS) {
        $shader->uniform1i('mode', 3);
    } elseif ($window->getKeyState(GLFW_KEY_2) === GLFW_PRESS) {
        $shader->uniform1i('mode', 1);
    } elseif ($window->getKeyState(GLFW_KEY_3) === GLFW_PRESS) {
        $shader->uniform1i('mode', 0);
    } elseif ($window->getKeyState(GLFW_KEY_4) === GLFW_PRESS) {
        $shader->uniform1i('mode', 2);
    }

    if ($window->getKeyState(GLFW_KEY_F) === GLFW_PRESS) {
        glPolygonMode(GL_FRONT_AND_BACK, GL_LINE);
    } else {
        glPolygonMode(GL_FRONT_AND_BACK, GL_FILL);
    }

    if ($window->getKeyState(GLFW_KEY_S) === GLFW_PRESS) {
        $shader->uniform1i('has_specular_texture', 0);
    } else {
        $shader->uniform1i('has_specular_texture', 1);
    }


    $transform1->rotation->y += 1;
    $transform1->__transformDirty = true;
    $shader->setTransformationMatrix($transform1->getMatrix());

	// draw the cube 1
   	$suzane->draw();

    // swap
    $window->swapBuffers();
    $window->pollEvents();

    $fl->wait();
}

