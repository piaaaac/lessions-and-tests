
<?php
$mapSrc = $page->distortionMaps()->toFiles()->first()->url();
$imgSrc = $page->distortionMaterial()->toFiles()->first()->url();
?>

<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="description" content="">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Untitled</title>
  <style type="text/css">
    body {margin: 0; background: white;}
/*     * {cursor: none;} */
  </style>
  <script src="<?= $site->url() ?>/assets/lib/p5.min.js"></script>
</head>
<body>  


<script id="vertex-shader" type="x-shader/x-vertex">
attribute vec3 aPosition;
attribute vec2 aTexCoord;
varying vec2 vTexCoord;

void main () {
  // copy the coordinates
  vTexCoord = aTexCoord;

  vec4 posVec4 = vec4(aPosition, 1.0);
  //posVec4.xy = posVec4.xy * 2.0 - 1.0;
  gl_Position = posVec4;
}
</script>
<script id="fragment-shader" type="notjx-shader/x-fragments">
#ifdef GL_ES
precision mediump float;
#endif

varying vec2 vTexCoord;
uniform sampler2D depthImage;
uniform sampler2D originalImage;
uniform vec2 mouse; // 0-1

void main() {
  vec2 uv = vTexCoord;
  uv.y = 1.0 - uv.y;

  vec4 depth = texture2D(depthImage, uv);

  
  gl_FragColor = texture2D(originalImage, uv + mouse*vec2(1.0, 1.0) * depth.g); // LARGE
  
  // gl_FragColor = texture2D(originalImage, uv + mouse*vec2(0.1, 0.05) * depth.g); // SMALL

  // gl_FragColor = texture2D(originalImage, uv + mouse*vec2(5.0, 5.0) * depth.g); // MASSIVE
}
</script>

<script>

let imgs = [
  {mapSrc: "<?= $mapSrc ?>", imgSrc: "<?= $imgSrc ?>"},
];
let img, imgDepthMap;
let myShader;

function preload () {
  let item = imgs[floor(random() * imgs.length)];
  img = loadImage(item.imgSrc);
  imgDepthMap = loadImage(item.mapSrc);
}

function setup () {
  createCanvas(window.innerWidth, window.innerHeight, WEBGL);
  noStroke();

  // create and initialize the shader
  var vertexShaderSource = document.querySelector("#vertex-shader").text;
  var fragmentShaderSource = document.querySelector("#fragment-shader").text;
  myShader = createShader(vertexShaderSource, fragmentShaderSource);
  shader(myShader);
  myShader.setUniform("originalImage", img);
  myShader.setUniform("depthImage", imgDepthMap);
}

function draw() {
  // myShader.setUniform("random", random());
  // myShader.setUniform("noise", noise(frameCount/100));
  myShader.setUniform("mouse", [mouseX/width-0.5, mouseY/height-0.5]);
  quad(-1, -1, 1, -1, 1, 1, -1, 1);
}    

</script>
</body>
</html>