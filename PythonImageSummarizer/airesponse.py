from typing import Optional

from dotenv import load_dotenv
from transformers import pipeline, BlipProcessor, BlipForConditionalGeneration
from PIL import Image
import torch


load_dotenv()


class HuggingFaceResponder:
    """Simple wrapper around a Hugging Face text generation pipeline."""

    def __init__(
        self,
        model: Optional[str] = None,
        task: str = "text-generation",
        device: int = -1,
    ):
        self.model = model or "gpt2"
        self.task = task
        self.device = device
        self.generator = pipeline(self.task, model=self.model, device=self.device)
        self.image_processor = None
        self.image_model = None

    def _init_image_model(self):
        """Lazy-load the BLIP image captioning model."""
        if self.image_processor is None:
            self.image_processor = BlipProcessor.from_pretrained("Salesforce/blip-image-captioning-base")
            self.image_model = BlipForConditionalGeneration.from_pretrained("Salesforce/blip-image-captioning-base")
            if self.device != -1:
                self.image_model = self.image_model.to(f"cuda:{self.device}" if torch.cuda.is_available() else "cpu")
            else:
                self.image_model = self.image_model.to("cpu")

    def create_response(
        self,
        prompt: str,
        instructions: Optional[str] = None,
        max_new_tokens: int = 128,
        do_sample: bool = True,
        temperature: float = 0.7,
        top_p: float = 0.9,
        **kwargs,
    ) -> str:
        if instructions:
            prompt = f"{instructions}\n\n{prompt}"

        output = self.generator(
            prompt,
            max_new_tokens=max_new_tokens,
            do_sample=do_sample,
            temperature=temperature,
            top_p=top_p,
            return_full_text=False,
            **kwargs,
        )

        if isinstance(output, list) and output:
            first = output[0]
            if isinstance(first, dict) and "generated_text" in first:
                return first["generated_text"].strip()
            return str(first).strip()

        return str(output).strip()

    def summarize_image(
        self,
        image_path: str,
        max_length: int = 50,
        do_sample: bool = True,
        temperature: float = 1.0,
        top_p: float = 0.9,
        top_k: Optional[int] = None,
        num_return_sequences: int = 1,
    ) -> str:
        """Generate a summary/caption for an image using BLIP.

        Sampling parameters (do_sample, temperature, top_p, top_k, num_return_sequences)
        can be provided to vary output across calls.
        """
        self._init_image_model()

        image = Image.open(image_path).convert("RGB")
        inputs = self.image_processor(image, return_tensors="pt")

        if self.device != -1 and torch.cuda.is_available():
            inputs = {k: v.to(f"cuda:{self.device}") for k, v in inputs.items()}

        gen_kwargs = {
            "max_length": max_length,
            "do_sample": do_sample,
            "temperature": temperature,
            "top_p": top_p,
            "num_return_sequences": num_return_sequences,
        }
        if top_k is not None:
            gen_kwargs["top_k"] = top_k

        with torch.no_grad():
            out = self.image_model.generate(**inputs, **gen_kwargs)

        # `out` may contain multiple sequences when `num_return_sequences > 1`.
        if num_return_sequences == 1:
            caption = self.image_processor.decode(out[0], skip_special_tokens=True)
            return caption.strip()

        captions = [self.image_processor.decode(o, skip_special_tokens=True).strip() for o in out]
        return "\n\n".join(captions)


if __name__ == "__main__":
    r = HuggingFaceResponder()
    example = (
        "Tell me a joke about cats and dogs."
    )
    out = r.create_response(example, instructions="You are a helpful assistant.")
    print(out)