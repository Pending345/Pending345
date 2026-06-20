"""Demo runner for the Hugging Face responder.

Usage:
    python aichattest.py "Tell me a joke"

This uses the Hugging Face transformers pipeline locally and does not require OpenAI.
"""
import sys
from airesponse import HuggingFaceResponder


def main():
    if len(sys.argv) > 1:
        prompt = " ".join(sys.argv[1:])
    else:
        prompt = input("Ask a question: ")

    r = HuggingFaceResponder()
    print("Sending prompt...\n")
    out = r.create_response(prompt, instructions="You are a helpful assistant.")
    print("\nResponse:\n")
    print(out)


if __name__ == "__main__":
    main()
