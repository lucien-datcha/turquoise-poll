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
            width:10px;
            height: 10px;
        }

        .circled {
            border : 1px solid #fff;
        }
    </style>
</head>

<body>
    <div id="app">
        <section class="section">
            <div class="container">
                <div class="columns">
                    <div class="column is-centered">
                        <h1 class="title">
                            Bonjour.
                        </h1>
                        <p class="subtitle">
                            Simple question, est-ce <strong>bleu</strong> ou <strong>vert</strong> ?
                        </p>
                        <div class="columns" id="scale"></div>
                        <p v-if="answers.length >= 60">
                            Le point encadré représente votre point de pivot vert / bleu. Vous percevez généralement ce qui se trouve à sa gauche comme vert, et ce qui se trouve à sa droite comme bleu.
                        </p>
                    </div>
                </div>
            </div>
        </section>
        <section class="section">
            <div class="container">
                <div class="columns is-centered">
                    <div class="column" v-if="answers.length < 60">
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
                            <div class="column is-1" v-for="answer in sortedAnswers">
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
    <script src="http://d3js.org/d3.v3.js"></script>

    <script>
        var vue = new Vue({
            el : '#app',
            data : {
                r : 0,
                g : 0,
                b : 0,
                colors: {},
                squareStyle : {
                    height: '200px',
                    width: '200px',
                    backgroundColor: 'rgb(0, 0, 0)'
                },
                answers : []
            },
            computed : {
                sortedAnswers : function() {
                    return this.answers.sort(function(a,b){
                        return (a.b > b.b) ? 1 : ((b.b > a.b) ? -1 : 0);
                    });
                }
            },
            methods : {
                send : function(data) {
                    axios.post('/api/answer/'+data, {
                        r : this.r,
                        g : this.g,
                        b : this.b
                    }).then(function(response){
                        this.vue.answers.push(
                            {r: this.vue.r, g: this.vue.g, b: this.vue.b, answer: data}
                        );

                        if(this.vue.answers.length === 60){
                            var moyG = 0;
                            var moyB = 0;

                            var nbG = 0;
                            var nbB = 0;

                            this.vue.answers.forEach(function(answer){
                                if(answer.answer === 'green') {
                                    moyG += answer.g;
                                    nbG++;
                                }
                                if(answer.answer === 'blue') {
                                    moyB += answer.b;
                                    nbB++;
                                }
                            });

                            moyG /= nbG;
                            moyB /= nbB;

                            var moy = '00'+moyG.toString(16).substr(0,2)+moyB.toString(16).substr(0, 2);

                            var scaleDiv = d3.select('#scale');
                            var length = 100;
                            var colors = d3.scale.linear().domain([1,length]).interpolate(d3.interpolateHcl).range([d3.rgb("#00FF87"), d3.rgb('#0087FF')]);

                            var toggle = true;
                            for (var i = 0; i < length; i++) {
                                if(parseInt(colors(i+1).substr(1, 6), 16) <= parseInt(moy, 16) && parseInt(colors(i-1).substr(1, 6), 16) >= parseInt(moy, 16)) {
                                    if(toggle){
                                        scaleDiv.append('div')
                                            .attr('style', function (d) {
                                                return 'background-color: ' + colors(i);
                                            })
                                            .attr('class', 'scale-node circled')
                                        ;
                                    }

                                    toggle = false;
                                } else {
                                    scaleDiv.append('div')
                                        .attr('style', function (d) {
                                            return 'background-color: ' + colors(i);
                                        })
                                        .attr('class', 'scale-node')
                                    ;
                                }
                            }
                        } else {
                            this.vue.g = 255 - Math.floor((Math.random() * 120) + 1);
                            this.vue.b = 255 - Math.floor((Math.random() * 120) + 1);
                            this.vue.squareStyle.backgroundColor = 'rgb(0,'+this.vue.g+', '+this.vue.b+')';
                        }
                    });
                },
                smallSquareStyle : function(answer){
                    return {
                        height : '50px',
                        width: '50px',
                        backgroundColor: 'rgb(0, '+answer.g+', '+answer.b+')'
                    }
                }
            },
            created : function(){

                this.g = 255 - Math.floor((Math.random() * 120) + 1);
                this.b = 255 - Math.floor((Math.random() * 120) + 1);

                this.squareStyle.backgroundColor = 'rgb(0,'+this.g+', '+this.b+')';
            }
        })
    </script>
</body>
</html>
