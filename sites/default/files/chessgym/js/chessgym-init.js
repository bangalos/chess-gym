
// required for debugging
//            document.onmousemove = function(e){
//                var x = e.pageX;
//                var y = e.pageY;
//                console.log("X is "+x+" and Y is "+y);
//            };

var INIT_TIMER = 15;
var memo;
var pgn;
//var movePov = "SECRETPOV";
//var fenStr = "SECRETFEN";

var all_moves, moveThreshold, counter, board, game, scratch_game, movePov, fenStr;
var fenEl, pgnEl, memEl, paused = 0, savedCall, gameInfo, moveColor;
var user_input_move = 'blank';
var fifteenTimer = 1;
var keymove_score = "Neutral";
var your_score= 0;
var total_moves = 0;
var game_ended = 0;
var plies_remaining = 0;

var map = {};

var playerNameElementMap = {}, playerImgElementMap = {};

function blinkPlayerImage() {
    resetPlayerImage();
    if (counter >= moveThreshold && moveColor == movePov) {
        if (movePov == "Black") {
            //                $('#circle_black').show();
            //                $('#circle_white').hide();
            $(playerImgElementMap['w']).stop();
            $(playerImgElementMap['w']).css({ opacity: 1.0 });
            blink($(playerImgElementMap['b']), 'slow');
        } else {
            $(playerImgElementMap['b']).stop();
            $(playerImgElementMap['b']).css({ opacity: 1.0 });
            //                $('#circle_white').show();
            //                $('#circle_black').hide();
            blink($(playerImgElementMap['w']), 'slow');
        }
    }
}

function resetPlayerImage() {
    $(playerImgElementMap['w']).stop();
    $(playerImgElementMap['w']).css({ opacity: 1.0 });
    $(playerImgElementMap['b']).stop();
    $(playerImgElementMap['b']).css({ opacity: 1.0 });
}

var init = function(memo_init, pgn_init, threshold, pov) {
    memo = memo_init;
    pgn = pgn_init;
    moveThreshold = threshold;
    movePov = pov;
    moveColor = movePov;
    
    var gameInfo = memo.split("<br/>");
    for (i=0;i<gameInfo.length;i++) {
        var strsplt = gameInfo[i].split("\"");
        map[strsplt[0].replace('[', '').trim()] = strsplt[1];
    }
    
    if (movePov == 'White') {
        playerNameElementMap = {'w' : '#player-x-name', 'b' : '#player-y-name'};
        playerImgElementMap = {'w' : '#player-x-img', 'b' : '#player-y-img'};
    } else {
        playerNameElementMap = {'b' : '#player-x-name', 'w' : '#player-y-name'};
        playerImgElementMap = {'b' : '#player-x-img', 'w' : '#player-y-img'};
    }

    scratch_game = new Chess(getKey('FEN'));
    statusEl = $('#status');
    fenEl = $('#fen');
    pgnEl = $('#pgn');
    userMoveEl = $('#user_move_html');
    gameMoveEl = $('#game_move_html');
    pliesEl = $('#plies_remaining_html');
    scoreEl = $('#your_score_html');
    
    game = new Chess(getKey('FEN'));
    var player_name;
    if (getKey('White') != null && getKey('Black') != null) {
        player_name = getKey('White').concat(" Vs ").concat(getKey('Black'));
    }
    
    playerNameEl = $('#player_name');
    
    //        $('#cnvsdiv').detach();
    // do not pick up pieces if the game is over
    // only pick up pieces for the side to move
    var onDragStart = function(source, piece, position, orientation) {
        if (game.game_over() === true ||
            (game.turn() === 'w' && piece.search(/^b/) !== -1) ||
            (game.turn() === 'b' && piece.search(/^w/) !== -1)) {
            return false;
        }
    };
    var onDrop = function(source, target) {
        // see if the move is legal
        var move = game.move({
                             from: source,
                             to: target,
                             promotion: 'q' // NOTE: always promote to a queen for example simplicity
                             });
        
        // legal move
        if (move != null) {
            var throwaway_history = game.history();
            user_input_move = throwaway_history[throwaway_history.length-1];
            game.undo();
            if (counter >= moveThreshold) {
                drawArrow(source, target, 0);
                real_move_to = all_moves[counter]
                setTimeout("updateArrow('"+(real_move_to.endsWith(user_input_move))+"');", 1500);
            }
            
            return 'snapback';
        }
        // illegal move
        if (move === null) return 'snapback';
        updateStatus();
    };
    // update the board position after the piece snap
    // for castling, en passant, pawn promotion
    var onSnapEnd = function() {
        board.position(game.fen());
    };
    var updateStatus = function() {
        $('#progressBar').hide();
        
        if(game_ended == 0) {
            $('.postgame').hide();
        } else {
            $('.postgame').show();
        }
        resetPlayerImage();
        
        var status = '';
        if (game.turn() === 'b') {
            moveColor = 'Black';
        } else {
            moveColor = 'White';
        }
        
        // checkmate?
        if (game.in_checkmate() === true) {
            status = 'Game over, ' + moveColor + ' is in checkmate.';
        }
        // draw?
        else if (game.in_draw() === true) {
            status = 'Game over, drawn position';
        }
        else if (game_ended == 1) {
            status = 'Game over';
        }
        // game still on
        else {
            status = moveColor + ' to move';
            // check?
            if (game.in_check() === true) {
                status += ', ' + moveColor + ' is in check';
            }
        }
        if (keymove_score == "Success") {
            your_score++;
            total_moves++;
            keymove_score = "Neutral";
        }
        if (keymove_score == "Fail") {
            total_moves++;
            keymove_score = "Neutral";
        }
        plies_remaining = all_moves.length - counter;
        //        score_html = "User move: " + user_input_move + "<br/>";
        //        score_html += "Game move: " + all_moves[counter-1] + "<br/>";
        //        score_html +=  "Plies remaining: " + plies_remaining + "<br/>Your Score: " + your_score + "/" + total_moves;
        user_move_html = user_input_move;
        game_move_html = all_moves[counter-1];
        plies_remaining_html = Math.floor((plies_remaining+1)/2);
        your_score_html = your_score + "/" + total_moves;
        statusEl.html(status);
        fenEl.html(game.fen());
        console.log(game.pgn());
        pgnEl.html(game.pgn());
        userMoveEl.html(user_move_html);
        gameMoveEl.html(game_move_html);
        pliesEl.html(plies_remaining_html);
        scoreEl.html(your_score_html);
        playerNameEl.html(player_name);
        console.log(game_ended);
        
        //        var blackPossibleMoves = filterMovesForSide(game.moves({legal: true, verbose: true, turn: 'b'}), 'b'); // all black moves
        //        var whitePossibleMoves = filterMovesForSide(game.moves({legal: true, verbose: true, turn: 'w'}), 'w'); // all white moves
        //        var allPossibleMoves = []
        //        allPossibleMoves.concat(blackPossibleMoves, whitePossibleMoves);
        var myPossibleMoves = filterMovesForSide(game.moves({legal: true, verbose: true}), game.turn());
        var mobility = getMobilityForPieces(myPossibleMoves);
        
        var mobilityStr = "";
        $(':not([data-square=""])').removeClass("highlight-mobility");
        for (var m = 0; m < mobility.length; m++) {
            var pieceMove = mobility[m]["move"];
            var aMobilityStr = pieceMove.piece + "/" + pieceMove.to + "=" + mobility[m]["cnt"];
            mobilityStr = mobilityStr + aMobilityStr + ";";
            $('*[data-square=' + pieceMove.from + ']').addClass("highlight-mobility");
        }
        
        $("#mobilityStr").html(mobilityStr);
        
        var movesByUniqueSpaces = filterMovesOnUniqueSpaces(myPossibleMoves);
        var opponentSpaces = filterMovesInOpponentCamp(movesByUniqueSpaces);
        $("#opponentSpacesCnt").html(opponentSpaces.length);
        
        if (game_ended) {
            score_to_record = 0;
            if (total_moves > 0) {
                score_to_record = Math.round( 100 * your_score / total_moves);
            }
            score_url = score_url + score_to_record;
            $.ajax({url: score_url, success: function(result){
                $("#game_review_record_status").html(result);
            }});
        }
    };
    var cfg = {
    draggable: true,
    position: getKey('FEN'),
    onDragStart: onDragStart,
    onDrop: onDrop,
    onSnapEnd: onSnapEnd,
    orientation: movePov
    };
    function highlightLastMove(move) {
        
        if(move != null) {
            $(':not([data-square=""])').removeClass("highlight-last-move");
            $('*[data-square=' + move.from + ']').addClass("highlight-last-move");
            $('*[data-square=' + move.to + ']').addClass("highlight-last-move");
        }
    }
    
    var makeMoveUntil = function () {
        if (!paused) {
            
            if (moveThreshold == 0 && counter == 0) {
                setTimeout(interactiveMove, 1000);
            } else {
                var moveInfo = game.move(all_moves[counter]);
                highlightLastMove(moveInfo);
                board.position(game.fen());
               // console.log(counter);
                //console.log(moveThreshold);
                counter++;
                if (counter < moveThreshold) {
                    setTimeout(makeMoveUntil, 1000);
                } else {
                    setTimeout(interactiveMove, 1000);
                }
            }
            updateStatus();
        } else {
            savedCall = function (){makeMoveUntil()};
        }
    }
    var interactiveMove = function () {
        if (!paused) {
          //  blinkPlayerImage();
            if (counter > 0) {
                //blinkPlayerImage();
                var moveInfo = game.move(all_moves[counter]);
                highlightLastMove(moveInfo);
                board.position(game.fen());
                counter++;
                updateStatus();
            }
            fifteenTimer = INIT_TIMER;
            keymove_score = "Neutral";
            
            setTimeout(function() { blinkPlayerImage(); $('#progressBar').show(); }, 500);
            progress(fifteenTimer);
            setTimeout(checkEverySecond, 1000);
            $("#countdown-holder").html(fifteenTimer);
            user_input_move = 'blank';
        } else {
            savedCall = function (){interactiveMove()};
        }
    }
    var checkEverySecond = function (){
        if (!paused) {
            fifteenTimer--;
            $("#countdown-holder").html(fifteenTimer);
            progress(fifteenTimer);
            if (user_input_move != 'blank') {
                if (user_input_move == all_moves[counter]) {
                    keymove_score = "Success";
                } else {
                    keymove_score = "Fail";
                }
                //blinkPlayerImage();
                var moveInfo = game.move(all_moves[counter]);
                highlightLastMove(moveInfo);
                board.position(game.fen());
                counter++;
                //updateStatus();
                if (counter < all_moves.length) {
                    //resetProgress();
                    updateStatus();
                    setTimeout(interactiveMove, 3000);
                } else {
                    game_ended = 1;
                    updateStatus();
                    //resetProgress();
                }
            } else {
                if (fifteenTimer > 0) {
                    if (counter >= all_moves.length) {
                        game_ended = 1;
                        //resetProgress();
                        updateStatus();
                    } else {
                        setTimeout(checkEverySecond, 1000);
                    }
                } else {
                    //blinkPlayerImage();
                    var moveInfo = game.move(all_moves[counter]);
                    highlightLastMove(moveInfo);
                    board.position(game.fen());
                    counter++;
                    keymove_score = "Fail";
                    if (counter >= all_moves.length) {
                        game_ended = 1;
                        //resetProgress();
                        updateStatus();
                    } else if (counter+2 < all_moves.length) {
                        //resetProgress();
                        updateStatus();
                        setTimeout(interactiveMove, 3000);
                    } else {
                        updateStatus();
                    }
                }
            }
        } else {
            savedCall = function (){checkEverySecond()};
        }
    }
    
    // movePov = SECRETPOV;
    board = ChessBoard('board', cfg);
    //console.log("pgn last" + pgn);
    scratch_game.load_pgn(pgn.join('\n'));
    //game.load_pgn(pgn.join('\n'));
    all_moves = scratch_game.history();
    //all_moves = game.history();
   // console.log(all_moves);
    initializeUIComponents();
    counter = 0;
    makeMoveUntil();
    updateStatus();
}; // end init()

// Function to get value of a key from a map
function getKey(k) {
    return map[k];
}

function flipPlayerInfoMap(playerMap) {
    var player_b_elem = playerMap['b'];
    playerMap['b'] = playerMap['w'];
    playerMap['w'] = player_b_elem;
}

// Function to Flip the board
function flipTheBoard() {
    board.orientation('flip');
    
    //     switchCSS('#player-y-name', '#player-x-name');
    //     switchCSS('#player-y-img', '#player-x-img');
    // switchCSS($('#player-y-name'), $('#player-y-name'));
    
    //var divWText = $(playerNameElementMap['w']).html();
    //var divBText = $(playerNameElementMap['b']).html();
    
    //$(playerNameElementMap['w']).html(divBText);
    //$(playerNameElementMap['b']).html(divWText);
    
    flipPlayerInfoMap(playerNameElementMap);
    flipPlayerInfoMap(playerImgElementMap);
    
    initializePlayerNames();
    blinkPlayerImage();
}
// Function to pause or resume the game
function pause() {
    if (paused) {
        paused = 0;
        //        $("#pauseBtn").html("Pause");
        $('#pauseBtn').attr('src', getUrlRoot() + '/sites/default/files/chessgym/images/pause.png');
        savedCall();
        savedCall = function() {};
    }
    else
    {
        paused = 1;
        //        $("#pauseBtn").html("Resume");
        $('#pauseBtn').attr('src', getUrlRoot() + '/sites/default/files/chessgym/images/play.png');
    }
}



// Function to get all the legal moves
function filterMovesForSide(moves, by) {
    var filteredMoves = [];
    for (var i = 0; i < moves.length; i++) {
        if (moves[i].color == by) {
            filteredMoves.push(moves[i]);
        }
    }
    return filteredMoves;
}



// Function to calculate unique spaces that moves can be made to
function getMobilityForPieces(moves) {
    var mobility = [];
    var MOBILITY_THRESHOLD = 3;
    var piecesMap = {};
    
    for (var i = 0; i < moves.length; i++) {
        if(moves[i].piece != 'p') {
            var pieceDataKey = moves[i].piece + ":" + moves[i].from;
            if(pieceDataKey in piecesMap) {
                piecesMap[pieceDataKey].push(moves[i]);
            }
            else {
                piecesMap[pieceDataKey] = [moves[i]];
            }
        }
    }
    
    for (var key in piecesMap) {
        if (piecesMap[key].length < MOBILITY_THRESHOLD) {
            var pieceDataArr = key.split(":");
            for (var l = 0; l < piecesMap[key].length; l++) {
                pieceMobilityData = {};
                pieceMobilityData["move"] = piecesMap[key][l];
                pieceMobilityData["cnt"] = piecesMap[key].length;
                mobility.push(pieceMobilityData);
            }
        }
    }
    
    return mobility;
}

// Function to calculate unique spaces that moves can be made to
function filterMovesOnUniqueSpaces(moves) {
    var filteredMoves = [];
    var foundSpaces = [];
    
    for (var i = 0; i < moves.length; i++) {
        if (foundSpaces.indexOf(moves[i].to) < 0) { // contains unique or not
            filteredMoves.push(moves[i]);
            foundSpaces.push(moves[i].to);
        }
    }
    
    return filteredMoves;
}

// Function to calculate spaces in opponent's camp
function filterMovesInOpponentCamp(moves) {
    var filteredMoves = [];
    for (var i = 0; i < moves.length; i++) {
        if (parseInt(moves[i].to.charAt(1), 10) > 4) { // check if rank greater than 4 to verify if on opponent's side
            filteredMoves.push(moves[i]);
        }
    }
    return filteredMoves;
}

function initializePlayerNames() {
    $(playerNameElementMap['w']).html(getKey('White'));
    $(playerNameElementMap['b']).html(getKey('Black'));
}

function updateArrow(isMoveCorrect) {
    $('#arrow-svg').css("animation", "");
    if (isMoveCorrect == 'true') {
        $('#arrow-head-path').css('fill', 'green');
        $('#arrow-path').css('stroke', 'green');
    } else {
        $('#arrow-head-path').css('fill', 'red');
        $('#arrow-path').css('stroke', 'red');
    }
    $('#arrow-path').stop();
    $('#arrow-path').css({ opacity: 1.0 });
    $('#arrow-head-path').stop();
    $('#arrow-head-path').css({ opacity: 1.0 });
    setTimeout("$('#arrow-svg').css('zIndex', -1);$('.chessboard-63f37').css('zIndex', 0);", 2000);
}

function drawArrow(from, to, width ) {
    fromDiv = $('*[data-square=' + from + ']');
    fromDivOffset = fromDiv.offset();
    fromX = parseInt(fromDivOffset.left + fromDiv.width() / 2);
    fromY = parseInt(fromDivOffset.top + fromDiv.height() / 2);
    
    toDiv = $('*[data-square=' + to + ']');
    toDivOffset = toDiv.offset();
    toX = parseInt(toDivOffset.left + toDiv.width() / 2);
    toY = parseInt(toDivOffset.top + toDiv.height() / 2);
    
    var arrowSvgOffset = $('#arrow-svg').offset();
    var path = document.getElementById('arrow-path');
    path.setAttribute('d', 'M'+(fromX-arrowSvgOffset.left)+','+(fromY-arrowSvgOffset.top)+' L'+(toX-arrowSvgOffset.left)+','+(toY-arrowSvgOffset.top));
    $('#arrow-svg').css("zIndex", 0);
    $('.chessboard-63f37').css("zIndex", -1);
    $('#arrow-head-path').css('fill', 'yellow');
    $('#arrow-path').css('stroke', 'yellow');
    blink($('#arrow-path'), 'fast');
    blink($('#arrow-head-path'), 'fast');
}

function blink(selector, speed){
    $(selector).fadeOut(speed, function(){
                        $(selector).fadeIn(speed, function(){
                                           blink(selector, speed);
                                           });
                        });
}
