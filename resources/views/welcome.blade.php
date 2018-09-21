<!doctype html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>turquoi.se</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.6.1/css/bulma.min.css">
    <style>
        .scale-node {
            width: 5px;
            height: 5px;
        }

        .avg-node {
            width: 5px;
            height: 5px;
            position: absolute;
            z-index: 1;
            transform: rotate(45deg) scale(0.2, 270);
        }
    </style>
</head>

<body>
<div id="app">
    <section class="section">
        <div class="container">
            <div class="columns">
                <div class="column is-centered">
                    <div class="columns">
                        <div class="column">
                            <h1 class="title">
                                Bonjour.
                            </h1>
                            <p class="subtitle">
                                Simple question, est-ce <strong>bleu</strong> ou <strong>vert</strong> ?
                            </p>
                        </div>
                    </div>
                    <div class="columns" v-show="answers.length === 24">
                        <div class="column is-half">
                            <div class="columns" id="color"></div>
                        </div>
                        <div class="column">
                            <p>
                                Cette droite représente votre pivot bleu / vert. Vous percevez ce qui se trouve "en
                                dessous" généralement en bleu, et "au dessus" en vert.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="section" v-if="answers.length < 24">
        <div class="container">
            <div class="columns is-centered">
                <div class="column">
                    <div class="columns">
                        <div v-bind:style="squareStyle"></div>
                    </div>
                    <div class="columns">
                        <div class="column is-centered">
                            <button class="button is-light is-large" @click="send('blue')">Bleu</button>
                            <button class="button is-light is-large" @click="send('green')">Vert</button>
                        </div>
                    </div>
                </div>
                <div class="column">
                    <div class="columns is-multiline">
                        <div class="column is-1" v-for="answer in answers">
                            <div v-bind:style="smallSquareStyle(answer)"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://unpkg.com/vue@2.5.9/dist/vue.js"></script>
<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="https://d3js.org/d3.v3.js"></script>

<script>
    var vue;
    vue = new Vue({
        el: '#app',
        data: {
            r: 0,
            g: 0,
            b: 0,
            moyG: 0,
            moyB: 0,
            colors: {},
            squareStyle: {
                height: '200px',
                width: '200px',
                backgroundColor: 'rgb(0, 0, 0)'
            },
            answers: []
        },
        methods: {
            send: function (data) {
                axios.post('/api/answer/' + data, {
                    r: this.r,
                    g: this.g,
                    b: this.b
                }).then(function (response) {
                    this.vue.answers.push(
                        {r: this.vue.r, g: this.vue.g, b: this.vue.b, answer: data}
                    );

                    if (this.vue.answers.length === 24) {
                        /*let nbG = 0;
                        let nbB = 0;

                        this.vue.answers.forEach(function (answer) {
                            if (answer.answer === 'green') {
                                this.vue.moyG += answer.g;
                                nbG++;
                            }
                            if (answer.answer === 'blue') {
                                this.vue.moyB += answer.b;
                                nbB++;
                            }
                        });

                        this.vue.moyG /= nbG;
                        this.vue.moyB /= nbB;

                        this.vue.moyG = Math.trunc(this.vue.moyG);
                        this.vue.moyB = Math.trunc(this.vue.moyB); */

                        let delta = 0;

                        this.vue.answers.forEach(function(answer) {
                            let deltatile = answer.g - answer.b;
                            let correct;

                            if(deltatile > 0) {
                                correct = 'green';
                            } else if (deltatile < 0) {
                                correct = 'blue'
                            }

                            if(answer.answer !== correct){
                                delta += deltatile
                            }
                        });

                        console.log(delta);

                        var colorScale = d3.select('#color');

                        for (var i = 127; i <= 255; i++) {
                            var scale = colorScale.append('div');
                            for (var n = 255; n >= 127; n--) {
                                var colors = d3.scale.linear()
                                    .domain([0, 127])
                                    .interpolate(d3.interpolateRgb)
                                    .range([d3.rgb(0, n, i), d3.rgb(0, n, i)])
                                ;

                                if (i === this.vue.avg.b && n === this.vue.avg.g) {
                                    scale.append('span')
                                        .attr('style', function (d) {
                                            return 'background-color: #fff';
                                        })
                                        .attr('class', 'avg-node')
                                    ;
                                }

                                if (i === 190 - delta && n === 190 - delta) {
                                    console.log("ici", 190 - delta);
                                    scale.append('span')
                                        .attr('style', function (d) {
                                            return 'background-color: #fff';
                                        })
                                        .attr('class', 'avg-node')
                                    ;
                                }

                                scale.append('div')
                                    .attr('style', function (d) {
                                        return 'background-color: ' + colors(n - 127);
                                    })
                                    .attr('class', 'scale-node')
                                ;
                            }
                        }

                    } else {
                        this.vue.g = 255 - Math.floor((Math.random() * 120) + 1);
                        this.vue.b = 255 - Math.floor((Math.random() * 120) + 1);
                        this.vue.squareStyle.backgroundColor = 'rgb(0,' + this.vue.g + ', ' + this.vue.b + ')';
                    }
                });
            },
            smallSquareStyle: function (answer) {
                return {
                    height: '50px',
                    width: '50px',
                    backgroundColor: 'rgb(0, ' + answer.g + ', ' + answer.b + ')'
                }
            }
        },
        created: function () {

            axios.get('/api/average').then(function (response) {
                this.vue.avg = response.data;
            });

            this.g = 255 - Math.floor((Math.random() * 120) + 1);
            this.b = 255 - Math.floor((Math.random() * 120) + 1);

            this.squareStyle.backgroundColor = 'rgb(0,' + this.g + ', ' + this.b + ')';
        }
    });
</script>
</body>
</html>
