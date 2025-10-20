<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200" :class="className" v-bind="$attrs">
        <!-- Official USMC Eagle, Globe, and Anchor Emblem -->
        
        <!-- Outer Rope Border -->
        <circle cx="100" cy="100" r="95" fill="none" stroke="#B8860B" stroke-width="10" 
                stroke-dasharray="8,4" opacity="0.9"/>
        
        <!-- Outer Black Ring -->
        <circle cx="100" cy="100" r="85" fill="#1a1a1a" stroke="#B8860B" stroke-width="2"/>
        
        <!-- Inner Red Circle -->
        <circle cx="100" cy="100" r="65" fill="#DC143C"/>
        
        <!-- Top Text Arc - DEPARTMENT OF THE NAVY -->
        <path id="topArc" d="M 35 100 A 65 65 0 0 1 165 100" fill="none"/>
        <text font-family="serif" font-size="9" font-weight="bold" fill="#B8860B" text-anchor="middle">
            <textPath href="#topArc" startOffset="50%">DEPARTMENT OF THE NAVY</textPath>
        </text>
        
        <!-- Bottom Text Arc - UNITED STATES MARINE CORPS -->
        <path id="bottomArc" d="M 165 100 A 65 65 0 0 1 35 100" fill="none"/>
        <text font-family="serif" font-size="9" font-weight="bold" fill="#B8860B" text-anchor="middle">
            <textPath href="#bottomArc" startOffset="50%">UNITED STATES MARINE CORPS</textPath>
        </text>
        
        <!-- Eagle at top -->
        <g transform="translate(100,65)">
            <!-- Eagle body -->
            <ellipse cx="0" cy="0" rx="8" ry="6" fill="#B8860B"/>
            <!-- Eagle head -->
            <circle cx="0" cy="-4" r="4" fill="#B8860B"/>
            <!-- Eagle beak -->
            <path d="M 0,-6 L 2,-8 L 0,-7 Z" fill="#B8860B"/>
            <!-- Left wing -->
            <path d="M -8,-2 Q -15,-8 -20,-2 Q -15,2 -8,0 Z" fill="#B8860B"/>
            <!-- Right wing -->
            <path d="M 8,-2 Q 15,-8 20,-2 Q 15,2 8,0 Z" fill="#B8860B"/>
            <!-- Eagle details -->
            <circle cx="-1" cy="-4" r="0.5" fill="#1a1a1a"/>
        </g>
        
        <!-- Globe in center -->
        <g transform="translate(100,100)">
            <!-- Globe sphere -->
            <circle cx="0" cy="0" r="20" fill="#C0C0C0" stroke="#808080" stroke-width="0.5"/>
            <!-- Globe grid lines - horizontal -->
            <ellipse cx="0" cy="-10" rx="18" ry="3" fill="none" stroke="#808080" stroke-width="0.5"/>
            <ellipse cx="0" cy="0" rx="20" ry="2" fill="none" stroke="#808080" stroke-width="0.5"/>
            <ellipse cx="0" cy="10" rx="18" ry="3" fill="none" stroke="#808080" stroke-width="0.5"/>
            <!-- Globe grid lines - vertical -->
            <ellipse cx="0" cy="0" rx="2" ry="20" fill="none" stroke="#808080" stroke-width="0.5"/>
            <ellipse cx="-10" cy="0" rx="3" ry="18" fill="none" stroke="#808080" stroke-width="0.5"/>
            <ellipse cx="10" cy="0" rx="3" ry="18" fill="none" stroke="#808080" stroke-width="0.5"/>
            <!-- Continents -->
            <path d="M -15,-5 Q -10,-8 -5,-5 Q -8,0 -12,2 Z" fill="#B8860B" opacity="0.7"/>
            <path d="M 5,-10 Q 12,-8 15,-3 Q 10,0 8,-8 Z" fill="#B8860B" opacity="0.7"/>
            <path d="M -8,8 Q -3,12 2,8 Q 0,15 -8,12 Z" fill="#B8860B" opacity="0.7"/>
        </g>
        
        <!-- Anchor behind globe -->
        <g transform="translate(100,135)">
            <!-- Anchor shaft -->
            <rect x="-1" y="-25" width="2" height="30" fill="#B8860B"/>
            <!-- Anchor ring -->
            <circle cx="0" cy="-23" r="3" fill="none" stroke="#B8860B" stroke-width="2"/>
            <!-- Anchor crossbar -->
            <rect x="-8" y="-18" width="16" height="2" fill="#B8860B"/>
            <!-- Left anchor fluke -->
            <path d="M -8,5 Q -15,8 -18,15 Q -15,18 -10,15 Q -8,10 -8,5 Z" fill="#B8860B"/>
            <!-- Right anchor fluke -->
            <path d="M 8,5 Q 15,8 18,15 Q 15,18 10,15 Q 8,10 8,5 Z" fill="#B8860B"/>
            <!-- Anchor stock (horizontal bar at top) -->
            <rect x="-12" y="-30" width="24" height="2" fill="#B8860B"/>
            <circle cx="-12" cy="-29" r="1.5" fill="#B8860B"/>
            <circle cx="12" cy="-29" r="1.5" fill="#B8860B"/>
        </g>
        
        <!-- Rope/Chain around anchor and globe -->
        <g transform="translate(100,100)">
            <!-- Left rope -->
            <path d="M -25,10 Q -30,20 -25,30 Q -20,35 -15,30 Q -20,20 -25,10" 
                  fill="none" stroke="#C0C0C0" stroke-width="3"/>
            <!-- Right rope -->
            <path d="M 25,10 Q 30,20 25,30 Q 20,35 15,30 Q 20,20 25,10" 
                  fill="none" stroke="#C0C0C0" stroke-width="3"/>
            <!-- Rope details -->
            <circle cx="-25" cy="15" r="1" fill="#C0C0C0"/>
            <circle cx="-22" cy="25" r="1" fill="#C0C0C0"/>
            <circle cx="25" cy="15" r="1" fill="#C0C0C0"/>
            <circle cx="22" cy="25" r="1" fill="#C0C0C0"/>
        </g>
        
        <!-- "Semper Fidelis" banner at top -->
        <g transform="translate(100,55)">
            <path d="M -15,-3 L 15,-3 L 12,3 L -12,3 Z" fill="#FF0000" opacity="0.8"/>
            <text x="0" y="1" font-family="serif" font-size="4" font-weight="bold" 
                  fill="white" text-anchor="middle">SEMPER FIDELIS</text>
        </g>
    </svg>
</template>
