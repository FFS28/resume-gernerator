import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:resume_flutter/models/invoice.dart';

abstract class Api {
  static String baseWebUrl = 'http://192.168.0.120:8000/';
  static String baseApiUrl = Api.baseWebUrl + 'api/';

  static login(String email, String password) async {
    /*final response = await http.get('https://support.oneskyapp.com/hc/en-us/article_attachments/202761627/example_1.json',
      headers: {HttpHeaders.acceptHeader: "application/json"}
    );*/
    final response = await http.post(Api.baseWebUrl + 'authentication_token',
      headers: {HttpHeaders.acceptHeader: "application/json"},
      body: {
        email: email,
        password: password,
      },
    );

    if (response.statusCode == 200) {
      debugPrint(response.request.url.toString());
      debugPrint(response.request.method.toString());
      debugPrint(response.body);
      //final Map<String, dynamic> data = json.decode(response.body);
      //debugPrint(data.toString());

      /*if (data['message']) {

      } else if (data['token']) {

      }*/
    } else {
      debugPrint(response.request.url.toString());
      debugPrint(response.request.method.toString());
      debugPrint(response.statusCode.toString());
      throw Exception('Failed to login');
    }
  }

  Future<Invoice> fetchInvoice(int id) async {
    final response = await http.get(Api.baseApiUrl + '/invoice/' + id.toString());

    if (response.statusCode == 200) {
      return Invoice.fromJson(json.decode(response.body));
    } else {
      throw Exception('Failed to load invoice');
    }
  }
}