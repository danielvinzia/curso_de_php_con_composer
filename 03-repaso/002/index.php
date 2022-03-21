<?php

// Asingnacion
$num = 9;
$lang = [
    'es' => 'español',
    'en' => 'ingles'
];

// Aritmetica
echo "Suma 2 + 2 " . ((int) 2 + (int) 2);
echo "Resta 2 - 2 " . ((int) 2 - (int) 2);
echo "Multiplica 2 * 2 " . 2 * 2;
echo "Divide 2 / 2 " . 2 / 2;
echo "Modulo 2 % 2 " . 2 % 2;
echo "Exponencial 2 ** 2 " . 2 ** 2;

// Comparacion
// igual ==, valor '9' == 9
// igual ===, valor - tipo 9 == 9
// Diferencias !=, valor
// Diferencias !==, valor - valor
// <, >, <=, >=

// Variables variables
$app = 'name';
$name = 'Daniel';
echo $app; // name
echo $$app; // Daniel
