<?php
$list = [];

/** @var \IXP\Models\Customer $c */
foreach( $t->customers as $c ) {
    unset( $data );
    $data['name']           = $c->name;
    $data['shortname']      = $c->shortname;
    $data['asn']            = $c->autsys;
    $data['ipv6']           = false;

    $macs = [];

    /** @var \IXP\Models\VirtualInterface $vi */
    foreach( $c->virtualInterfaces as $vi ) {
        $pis = $vi->physicalInterfaces;

        if( !count( $pis ) ) {
            continue;
        }

        /** @var \IXP\Models\VlanInterface $vli */
        foreach( $vi->vlanInterfaces as $vli ) {
            /** @var \IXP\Models\Layer2Address $mac */
            foreach( $vli->layer2addresses as $mac ) {
                $macs[] = $mac->macFormatted( ':' );
            }
            if( count( $macs ) ) {
                $data[ 'layer2addresses' ] = $macs;
            }
            if( $vli->ipv6enabled ) {
                $data[ 'ipv6' ] = true;
            }
        }
    }

    $list[] = $data;
}

echo json_encode( $list, JSON_PRETTY_PRINT );
