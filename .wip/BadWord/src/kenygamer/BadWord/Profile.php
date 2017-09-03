<?php

/*
 * BadWord plugin for PocketMine-MP
 * Copyright (C) 2017 Kevin Andrews <https://github.com/kenygamer/pmmp-plugins/blob/master/BadWord>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
*/

namespace kenygamer\BadWord;

class Profile{
  
  /** @var string */
  private $name;
  /** @var array */
  private $words;
  
  public function __construct(string $name, array $words){
    $this->name = $name;
    $this->words = $words;
  }
  
  /**
   * Returns profile name
   *
   * @return string
   */
  public function getName() : string{
    return $this->name;
  }
  
  /**
   * Returns profile words
   *
   * @param bool $all If false return only approved words
   *
   * @return array
   */
  public function getWords(bool $all = true) : array{
    if($all){
      return $this->words;
    }
    $words = [];
    foreach($this->words as $word){
      if($word[1]){
        $words[] = $word;
      }
    }
    return (array) $words;
  }
  
  /**
   * Removes all profile words
   *
   * @return void
   */
  public function unsetWords(){
    $this->words = [];
  }
  
  /**
   * Adds a word to profile
   *
   * @param string $word
   *
   * @return void
   */
  public function setWord(string $word){
    $this->words[] = $word;
  }
  
}
