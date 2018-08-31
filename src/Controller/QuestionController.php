<?php

namespace App\Controller;

use App\Entity\Question;
use App\Form\QuestionType;
use App\Entity\Answer;
use App\Form\AnswerType;
use App\Repository\QuestionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/question")
 */
class QuestionController extends Controller
{
    /**
     * @Route("/", name="question_index", methods="GET")
     */
    public function index(QuestionRepository $questionRepository): Response
    {
        return $this->render('question/index.html.twig', ['questions' => $questionRepository->findAll()]);
    }

    /**
     * @Route("/new", name="question_new", methods="GET|POST")
     */
    public function new(Request $request): Response
    {
        $question = new Question();
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('question_index');
        }

        return $this->render('question/new.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="question_show", methods="GET")
     */
    public function show(Question $question): Response
    {
        return $this->render('question/show.html.twig', ['question' => $question]);
    }

    /**
     * @Route("/{id}/edit", name="question_edit", methods="GET|POST")
     */
    public function edit(Request $request, Question $question): Response
    {
        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        // formulaire d'ajout d'une réponse
        $answer = new Answer();
        $formAnswer = $this->createForm(AnswerType::class, $answer);
        $formAnswer->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('question_edit', ['id' => $question->getId()]);
        }
        if ($formAnswer->isSubmitted() && $formAnswer->isValid()) {
          $question->addAnswer($answer);
          $em = $this->getDoctrine()->getManager();
          $em->persist($answer);
          $em->flush();

        }

        return $this->render('question/edit.html.twig', [
            'question' => $question,
            'form' => $form->createView(),
            'formAnswer' => $formAnswer->createView()
        ]);
    }

    /**
     * @Route("/{id}", name="question_delete", methods="DELETE")
     */
    public function delete(Request $request, Question $question): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($question);
            $em->flush();
        }

        return $this->redirectToRoute('question_index');
    }
}
