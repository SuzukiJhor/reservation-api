<?php

namespace App;

enum EventStatus: string
{
    case PENDENTE = 'pendente';
    case CONFIRMADO = 'confirmado';
    case CANCELADO = 'cancelado';
    case EXPIRADO = 'expirado';
}
