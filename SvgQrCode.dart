import 'package:flutter/material.dart';

class PngQrCode extends StatelessWidget {
  final String url;
  final double? width;
  final double? height;

  const PngQrCode({Key? key, required this.url, this.width, this.height})
    : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Image.network(
      url,
      width: width,
      height: height,
      errorBuilder: (context, error, stackTrace) =>
          const Center(child: Text("Erro ao carregar imagem PNG")),
    );
  }
}
