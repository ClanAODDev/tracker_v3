<?php

namespace App\Enums;

enum RecommendationDecision:string
{
    case APPROVED = "approved";
    case DENIED = "denied";
}