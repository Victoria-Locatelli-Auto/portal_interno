<?php

function noticeDateLabel(?string $date): string
{
    if (empty($date)) {
        return '-';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('d/m/Y', $timestamp) : $date;
}

function noticeDateTimeAttribute(?string $date): string
{
    if (empty($date)) {
        return '';
    }

    $timestamp = strtotime($date);

    return $timestamp ? date('c', $timestamp) : $date;
}
