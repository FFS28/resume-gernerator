import 'dart:convert';

import 'package:kitchen_party/RecipeIngredient.dart';

class Recipe {
  final int id;
  final String name;
  final String imagePath;


  final bool isVege;
  final bool isVegan;
  final bool hasMeat;
  final bool hasFish;
  final bool isSweet;
  final bool isSalty;

  final List<String> instructions;
  final int nbSlices;
  final int cookingDuration;
  final int preparationDuration;
  final int waigingDuration;
  final String kitchen;
  final bool isAllInKitchen;

  final List<RecipeIngredient> recipeIngredients;
  final List<int> ingredientIds;

  Recipe({
    this.id,
    this.name,
    this.imagePath,
    this.isVege,
    this.isVegan,
    this.hasMeat,
    this.hasFish,
    this.isSweet,
    this.isSalty,
    this.instructions,
    this.nbSlices,
    this.preparationDuration,
    this.cookingDuration,
    this.waigingDuration,
    this.kitchen,
    this.isAllInKitchen,
    this.recipeIngredients,
    this.ingredientIds
  });

  factory Recipe.fromJson(Map<String, dynamic> json) {
    final List<RecipeIngredient> recipeIngredients = [];
    json['recipeIngredients'].forEach((element) {
      recipeIngredients.add(RecipeIngredient.fromJson(element));
    });
    final List<int> ingredientIds = [];
    json['ingredientIds'].forEach((element) {
      ingredientIds.add(element);
    });
    final List<String> instructions = [];
    json['instructions'].forEach((element) {
      instructions.add(element);
    });

    return Recipe(
      id: json['id'],
      name: json['name'],
      imagePath: json['imagePath'],
      isVege: json['vege'],
      isVegan: json['vegan'],
      hasMeat: json['meat'],
      hasFish: json['fish'],
      isSalty: json['salty'],
      isSweet: json['sweet'],
      instructions: instructions,
      nbSlices: json['nbSlices'],
      preparationDuration: json['preparationDuration'],
      cookingDuration: json['cookingDuration'],
      waigingDuration: json['waigingDuration'],
      kitchen: json['kitchen'],
      isAllInKitchen: json['allKitchen'],
      recipeIngredients: recipeIngredients,
      ingredientIds: ingredientIds,
    );
  }
}