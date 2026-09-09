<?php

if (! function_exists('calapan_barangays')) {
    /**
     * The 62 barangays of Calapan City, Oriental Mindoro — used to
     * populate the Barangay dropdown on Register and the Profile pages
     * (source: PhilAtlas). Anyone outside Calapan picks "Other" instead,
     * which reveals a free-text field.
     */
    function calapan_barangays(): array
    {
        return [
            'Balingayan', 'Balite', 'Baruyan', 'Batino', 'Bayanan I', 'Bayanan II',
            'Biga', 'Bondoc', 'Bucayao', 'Buhuan', 'Bulusan', 'Calero', 'Camansihan',
            'Camilmil', 'Canubing I', 'Canubing II', 'Comunal', 'Guinobatan', 'Gulod',
            'Gutad', 'Ibaba East', 'Ibaba West', 'Ilaya', 'Lalud', 'Lazareto', 'Libis',
            'Lumang Bayan', 'Mahal na Pangalan', 'Maidlang', 'Malad', 'Malamig',
            'Managpi', 'Masipit', 'Nag-iba I', 'Nag-iba II', 'Navotas', 'Pachoca',
            'Palhi', 'Panggalaan', 'Parang', 'Patas', 'Personas', 'Putingtubig',
            'Salong', 'San Antonio', 'San Vicente Central', 'San Vicente East',
            'San Vicente North', 'San Vicente South', 'San Vicente West', 'Santa Cruz',
            'Santa Isabel', 'Santa Maria Village', 'Santa Rita', 'Santo Niño', 'Sapul',
            'Silonay', 'Suqui', 'Tawagan', 'Tawiran', 'Tibag', 'Wawa',
        ];
    }
}
