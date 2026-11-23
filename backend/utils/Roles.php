<?php
/** The two roles of FitnessTracker (users.role). Written once, used everywhere. */
class Roles
{
    const ADMIN = 'admin';
    const USER = 'user';

    const ALL = [self::ADMIN, self::USER];
}
