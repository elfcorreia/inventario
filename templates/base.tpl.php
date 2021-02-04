<html>
    <head>
        <style>
            body {
                background-color: lightgray;
                font-family: sans;                
            }
            .dashboard {
                display: flex;
                flex-direction: column;
            }
            .dashboard h1 {
                text-transform: uppercase;
                font-size: 14pt;                
            }
            .dashboard > div {
                
            }
            .dashboard > div > * {
                margin-left: 12px;
            }
            .dashboard > div > *:first-child {
                margin-left: 0;
            }
            .dashboard .item {
                background-color: white;
                border-radius: 8px;
                margin-left: 12px;
                border: solid 1pt gray;
                padding: 0.75em;
                height: 4em;
                min-width: 6em;
            }
        </style>
    </head>
<body style="max-width: 768px; margin: auto;">
    <?php $block('content') ?><?php $end_block() ?>    
</body>
</html>