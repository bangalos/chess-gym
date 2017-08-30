//
//  ViewController.m
//  Chess-Gym
//
//  Created by Garsh on 8/23/16.
//  Copyright (c) 2016 ChessGym. All rights reserved.
//

#import "ViewController.h"

@interface ViewController ()

@end

@implementation ViewController

- (void)viewDidLoad
{
    [super viewDidLoad];
	// Do any additional setup after loading the view, typically from a nib.
    //NSString *fullURL = @"http://www.chess-gym.com";
    NSString *fullURL = @"http://www.chess-gym.com/";
    NSURL *url = [NSURL URLWithString:fullURL];
    NSURLRequest *requestObj = [NSURLRequest requestWithURL:url];
    [_viewWeb loadRequest:requestObj];
    self.viewWeb.scalesPageToFit = YES;
    self.viewWeb.contentMode = UIViewContentModeScaleAspectFit;
    self.viewWeb.frame=self.view.bounds;
    [[self navigationController] setNavigationBarHidden:YES animated:YES];
}

- (void)didReceiveMemoryWarning
{
    [super didReceiveMemoryWarning];
    // Dispose of any resources that can be recreated.
}

- (BOOL)prefersStatusBarHidden {
    return YES;
}

@end
