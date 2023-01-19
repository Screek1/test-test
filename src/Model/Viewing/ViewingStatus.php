<?php


namespace App\Model\Viewing;


class ViewingStatus
{
    const New = 'new';
    const Schedules = 'scheduled';
    const CANCELED = 'canceled';

    const All = [ViewingStatus::New, ViewingStatus::Schedules, ViewingStatus::CANCELED];
}